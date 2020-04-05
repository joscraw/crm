<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Spreadsheet;
use App\Form\ImportMappingType;
use App\Http\ApiResponse;
use App\Message\ImportSpreadsheet;
use App\Repository\CustomObjectRepository;
use App\Repository\FilterRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\PhpSpreadsheetHelper;
use App\Service\UploaderHelper;
use App\Service\WorkflowProcessor;
use App\Utils\ArrayHelper;
use App\Utils\FormHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\ServiceHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class ImportController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/import")
 */
class ImportController extends ApiController
{
    use MultiDimensionalArrayExtractor;
    use ArrayHelper;
    use ServiceHelper;
    use FormHelper;

    /**
     * RecordController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     * @param SerializerInterface $serializer
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     * @param FilterRepository $filterRepository
     * @param WorkflowProcessor $workflowProcessor
     * @param MessageBusInterface $bus
     * @param PhpSpreadsheetHelper $phpSpreadsheetHelper
     * @param UploaderHelper $uploadHelper
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository,
        SerializerInterface $serializer,
        PermissionAuthorizationHandler $permissionAuthorizationHandler,
        FilterRepository $filterRepository,
        WorkflowProcessor $workflowProcessor,
        MessageBusInterface $bus,
        PhpSpreadsheetHelper $phpSpreadsheetHelper,
        UploaderHelper $uploadHelper
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
        $this->serializer = $serializer;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
        $this->filterRepository = $filterRepository;
        $this->workflowProcessor = $workflowProcessor;
        $this->bus = $bus;
        $this->phpSpreadsheetHelper = $phpSpreadsheetHelper;
        $this->uploadHelper = $uploadHelper;
    }

    /**
     * @Route("/mapping", name="api_import_mapping", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return ApiResponse|string|JsonResponse
     */
    public function importMapping(Portal $portal, Request $request) {

        $form = $this->createForm(ImportMappingType::class, null, [
            'portal' => $portal,
            'validation_groups' => [
                'MAPPING'
            ]
        ]);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var File $uploadedFile */
            $uploadedFile = $form->get('file')->getData();
            /** @var CustomObject $customObject */
            $customObject = $form->get('customObject')->getData();

            try {
                $columns = $this->phpSpreadsheetHelper->getColumns($uploadedFile);
            } catch (\Exception $exception) {
                return new ApiResponse($exception->getMessage(), [
                    'success' => false,
                ], [], 400);
            }

            $properties = [];
            $properties['Unmapped'] = 'unmapped';
            foreach($customObject->getProperties() as $property) {
                $properties[$property->getLabel()] = $property->getInternalName();
            }

            return new JsonResponse([
                'success' => true,
                'mappings' => [
                    'mapped_from' => $columns,
                    'mapped_to' => $properties
                ]
            ], Response::HTTP_OK);
        }

        $errors = $this->getErrorsFromForm($form);
        return new ApiResponse("Error fetching mapping.", [
            'success' => false,
        ], $errors, 400);
    }

    /**
     * @Route("", name="api_import", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return ApiResponse|JsonResponse
     */
    public function import(Portal $portal, Request $request) {

        $form = $this->createForm(ImportMappingType::class, null, [
            'portal' => $portal,
            'validation_groups' => [
                'IMPORT'
            ]
        ]);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $importData = $form->getData();
            /** @var File $file */
            $file = $form->get('file')->getData();

            /** @var CustomObject $customObject */
            $customObject = $form->get('customObject')->getData();
            $originalFileName = $form->get('originalFileName')->getData();
            $newFilename = $this->uploadHelper->uploadSpreadsheet($file);
            // For security reasons symfony uses the following method to determine file extension
            // https://www.tutorialfor.com/questions-41236.htm
            // This can cause issues guessing whether or not it's a csv file
            if(pathinfo (basename ($newFilename)) ['extension'] === 'csv') {
                $mimeType = 'text/csv';
            } else {
                $mimeType = $file->getMimeType();
            }

            $spreadsheet = new Spreadsheet();
            $spreadsheet->setCustomObject($customObject);
            $spreadsheet->setOriginalName($originalFileName ?? $newFilename);
            $spreadsheet->setMimeType($mimeType ?? 'application/octet-stream');
            $spreadsheet->setFileName($newFilename);
            $spreadsheet->setMappings($form->get('mappings')->getData());
            $this->entityManager->persist($spreadsheet);
            $this->entityManager->flush();
            $this->bus->dispatch(new ImportSpreadsheet($spreadsheet->getId()));

            return new ApiResponse("File successfully queued for import.", [
                'success' => true,
            ], [], 200);
        }

        $errors = $this->getErrorsFromForm($form);
        return new ApiResponse("Error queuing import.", [
            'success' => false,
        ], $errors, 400);
    }
}