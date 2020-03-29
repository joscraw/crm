<?php

namespace App\Controller;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Filter;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Entity\Role;
use App\Entity\Spreadsheet;
use App\Form\BulkEditType;
use App\Form\CustomObjectType;
use App\Form\DeleteRecordType;
use App\Form\ImportMappingType;
use App\Form\ImportRecordType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RecordType;
use App\Form\SaveFilterType;
use App\Http\ApiResponse;
use App\Message\ImportSpreadsheet;
use App\Message\WorkflowMessage;
use App\Model\FieldCatalog;
use App\Model\Filter\FilterData;
use App\Repository\CustomObjectRepository;
use App\Repository\FilterRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\MessageGenerator;
use App\Service\PhpSpreadsheetHelper;
use App\Service\UploaderHelper;
use App\Service\WorkflowProcessor;
use App\Utils\ArrayHelper;
use App\Utils\FormHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\ServiceHelper;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class ImportController
 * @package App\Controller
 *
 * @Route("{internalIdentifier}/import")
 */
class ImportController extends AbstractController
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
     * @Route("/{id}/form", name="import_form", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function importForm(Portal $portal, CustomObject $customObject, Request $request) {
        $form = $this->createForm(ImportRecordType::class, null, [
            'customObject' => $customObject
        ]);
        $form->handleRequest($request);
        $formMarkup = $this->renderView(
            'Api/form/record_import_form.html.twig',
            [
                'form' => $form->createView(),
                'columns' => []
            ]
        );
        if ($form->isSubmitted() && $form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/record_import_form.html.twig',
                [
                    'form' => $form->createView()
                ]
            );
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            return new JsonResponse([
                'success' => true,
                'formMarkup' => $formMarkup,
            ], Response::HTTP_BAD_REQUEST);
        }
        return new JsonResponse([
            'success' => true,
            'formMarkup' => $formMarkup,
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{internalName}/import", name="import", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function importAction(Portal $portal, CustomObject $customObject, Request $request) {
        $user = $this->getUser();
        $form = $this->createForm(ImportRecordType::class, null, [
            'customObject' => $customObject
        ]);
        $form->handleRequest($request);
        $formMarkup = $this->renderView(
            'Api/form/record_import_form.html.twig',
            [
                'form' => $form->createView()
            ]
        );

        if($form->isSubmitted() && $form->isValid()) {
            $importData = $form->getData();
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();
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
            $spreadsheet->setOriginalName($file->getClientOriginalName() ?? $newFilename);
            $spreadsheet->setMimeType($mimeType ?? 'application/octet-stream');
            $spreadsheet->setFileName($newFilename);
            $spreadsheet->setMappings($form->get('mappings')->getData());
            $this->entityManager->persist($spreadsheet);
            $this->entityManager->flush();
            $this->bus->dispatch(new ImportSpreadsheet($spreadsheet->getId()));
            return new JsonResponse([
                'success' => true,
                'formMarkup' => $formMarkup,
            ], Response::HTTP_OK);
        }
        return new JsonResponse(
            [
                'success' => false,
                'formMarkup' => $formMarkup,
            ], Response::HTTP_BAD_REQUEST
        );
    }
}