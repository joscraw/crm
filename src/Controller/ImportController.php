<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Spreadsheet;
use App\Form\ImportRecordType;
use App\Message\ImportSpreadsheetMessage;
use App\Utils\ArrayHelper;
use App\Utils\FormHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\ServiceHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


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
     * @Route("/{internalName}/mapping", name="import_mapping", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function importMapping(Portal $portal, CustomObject $customObject, Request $request) {
        $form = $this->createForm(ImportRecordType::class, null, [
            'customObject' => $customObject,
            'validation_groups' => [
                'MAPPING'
            ]
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
            'customObject' => $customObject,
            'validation_groups' => [
                'MAPPING',
                'IMPORT'
            ]
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
            $this->bus->dispatch(new ImportSpreadsheetMessage($spreadsheet->getId()));
            return new JsonResponse([
                'success' => true,
                'formMarkup' => $formMarkup,
            ], Response::HTTP_OK);
        }

        $formMarkup = $this->renderView(
            'Api/form/record_import_form.html.twig',
            [
                'form' => $form->createView()
            ]
        );

        return new JsonResponse(
            [
                'success' => false,
                'formMarkup' => $formMarkup,
            ], Response::HTTP_BAD_REQUEST
        );
    }
}