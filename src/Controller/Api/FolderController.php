<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\Folder;
use App\Entity\Portal;
use App\Form\DeleteFolderType;
use App\Form\EditFolderType;
use App\Repository\CustomObjectRepository;
use App\Repository\FolderRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use App\Utils\ArrayHelper;
use App\Utils\ListFolderBreadcrumbs;
use App\Utils\MultiDimensionalArrayExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;


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

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @var RecordRepository
     */
    private $recordRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ReportRepository
     */
    private $reportRepository;

    /**
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * @var MarketingListRepository
     */
    private $marketingListRepository;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * @var ListFolderBreadcrumbs
     */
    private $folderBreadcrumbs;

    /**
     * ListController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RecordRepository $recordRepository
     * @param SerializerInterface $serializer
     * @param ReportRepository $reportRepository
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     * @param MarketingListRepository $marketingListRepository
     * @param FolderRepository $folderRepository
     * @param ListFolderBreadcrumbs $folderBreadcrumbs
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository,
        SerializerInterface $serializer,
        ReportRepository $reportRepository,
        PermissionAuthorizationHandler $permissionAuthorizationHandler,
        MarketingListRepository $marketingListRepository,
        FolderRepository $folderRepository,
        ListFolderBreadcrumbs $folderBreadcrumbs
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
        $this->serializer = $serializer;
        $this->reportRepository = $reportRepository;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
        $this->marketingListRepository = $marketingListRepository;
        $this->folderRepository = $folderRepository;
        $this->folderBreadcrumbs = $folderBreadcrumbs;
    }


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