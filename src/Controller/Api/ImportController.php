<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Spreadsheet;
use App\Form\ImportMappingType;
use App\Http\ApiResponse;
use App\Message\ImportSpreadsheetMessage;
use App\Utils\ArrayHelper;
use App\Utils\FormHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\ServiceHelper;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


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
            $this->bus->dispatch(new ImportSpreadsheetMessage($spreadsheet->getId()));

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