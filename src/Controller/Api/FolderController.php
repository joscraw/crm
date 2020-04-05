<?php

namespace App\Controller\Api;

use App\Entity\Folder;
use App\Entity\Portal;
use App\Form\DeleteFolderType;
use App\Form\EditFolderType;
use App\Utils\ArrayHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\ServiceHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class FolderController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/folders")
 */
class FolderController extends ApiController
{
    use MultiDimensionalArrayExtractor;
    use ArrayHelper;
    use ServiceHelper;

    /**
     * @Route("/{folderId}/edit", name="edit_folder", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Folder $folder
     * @param Request $request
     * @return JsonResponse
     */
    public function editFolderAction(Portal $portal, Folder $folder, Request $request) {

        $form = $this->createForm(EditFolderType::class, $folder);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/edit_folder_form.html.twig',
            [
                'form' => $form->createView()
            ]
        );

        if ($form->isSubmitted() && !$form->isValid()) {

            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $j = $form->isSubmitted();


        if($j) {

            $k = $form->isValid();
        }

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var $folder Folder */
            $folder = $form->getData();
            $this->entityManager->persist($folder);
            $this->entityManager->flush();
        }

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{folderId}/delete-form", name="delete_folder_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Folder $folder
     * @return JsonResponse
     */
    public function getDeleteFolderFormAction(Portal $portal, Folder $folder) {

        $form = $this->createForm(DeleteFolderType::class, $folder);

        $formMarkup = $this->renderView(
            'Api/form/delete_folder_form.html.twig',
            [
                'form' => $form->createView(),
            ]
        );

        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{folderId}/delete", name="delete_folder", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Folder $folder
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteFolderAction(Portal $portal, Folder $folder, Request $request)
    {

        $form = $this->createForm(DeleteFolderType::class, $folder);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_folder_form.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );

            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        foreach($folder->getMarketingLists() as $list) {
            $list->setFolder(null);
            $this->entityManager->persist($list);
        }

        // delete folder here
        $this->entityManager->remove($folder);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );

    }
}