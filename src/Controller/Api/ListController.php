<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\Folder;
use App\Entity\MarketingList;
use App\Entity\Portal;
use App\Entity\Role;
use App\Form\DeleteListType;
use App\Form\FolderType;
use App\Form\MoveListToFolderType;
use App\Model\FieldCatalog;
use App\Utils\ArrayHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\ServiceHelper;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class ListController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/lists")
 */
class ListController extends ApiController
{
    use MultiDimensionalArrayExtractor;
    use ArrayHelper;
    use ServiceHelper;

    /**
     * @Route("/list-types", name="get_list_types", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getListTypesAction(Portal $portal, Request $request) {

        $payload['list_types'] = MarketingList::$LIST_TYPES;

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/create-folder", name="create_list_folder", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function createFolderAction(Portal $portal, Request $request) {

        $folder = new Folder();

        $form = $this->createForm(FolderType::class, $folder);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/folder_form.html.twig',
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

        if ($form->isSubmitted() && $form->isValid()) {

            $folderId = $request->request->get('folderId', null);


            /** @var $folder Folder */
            $folder = $form->getData();

            if($folderId) {
                $parentFolder = $this->folderRepository->find($folderId);
                $folder->setParentFolder($parentFolder);
            }

            $folder->setPortal($portal);
            $folder->setType(Folder::LIST_FOLDER);
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
     * @Route("/{listId}/move-to-folder", name="move_list_to_folder", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param MarketingList $list
     * @param Request $request
     * @return JsonResponse
     */
    public function moveListToFolderAction(Portal $portal, MarketingList $list, Request $request) {

        $form = $this->createForm(MoveListToFolderType::class, null, [
            'portal' => $portal
        ]);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/move_list_to_folder_form.html.twig',
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

        if ($form->isSubmitted() && $form->isValid()) {

            $folderId = $form->get('folder')->getData();

            if($folderId) {

                $folder = $this->folderRepository->find($folderId);
                $list->setFolder($folder);
            } else {
                $list->setFolder(null);
            }


            $this->entityManager->persist($list);
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
     * @Route("/{internalName}/save-list", name="save_list", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function saveListAction(Portal $portal, CustomObject $customObject, Request $request) {
        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::CREATE_LIST,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }
        $data = $request->request->get('data', []);
        $listName = $data['listName'];
        $query = $this->recordRepository->newReportLogicBuilder($data, $customObject, true);
        $list = new MarketingList();
        $list->setQuery($query);
        $list->setCustomObject($customObject);
        $list->setData($data);
        $list->setName($listName);
        $list->setPortal($portal);
        $this->entityManager->persist($list);
        $this->entityManager->flush();
        $response = new JsonResponse([
            'success' => true,
            'listId' => $list->getId()
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/datatable", name="lists_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getListsForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->query->get('draw'));
        $start = $request->query->get('start');
        $length = $request->query->get('length');
        $search = $request->query->get('search');
        $orders = $request->query->get('order');
        $columns = $request->query->get('columns');

        $results = $this->marketingListRepository->getDataTableData($portal, $start, $length, $search, $orders, $columns);

        $totalListCount = $this->marketingListRepository->getTotalCount($portal);
        $arrayResults = $results['arrayResults'];
        $filteredListCount = count($arrayResults);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) ? $filteredListCount : $totalListCount,
            'recordsTotal'  => $totalListCount,
            'data'  => $arrayResults
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/folders/datatable", name="list_folders_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getFoldersForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->query->get('draw'));
        $start = $request->query->get('start');
        $length = $request->query->get('length');
        $search = $request->query->get('search');
        $orders = $request->query->get('order');
        $columns = $request->query->get('columns');
        $folderId = $request->query->get('folderId', null);

        $folderResults = $this->folderRepository->getDataTableData($portal, $start, $length, $search, $orders, $columns, $folderId);

        $listResults = $this->marketingListRepository->getDataTableDataForFolder($portal, $start, $length, $search, $orders, $columns, $folderId);

        $totalListCount = $this->marketingListRepository->getTotalCount($portal);

        $arrayResults = array_merge($folderResults['arrayResults'], $listResults['arrayResults']);

        $filteredListCount = count($arrayResults);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) ? $filteredListCount : $totalListCount,
            'recordsTotal'  => $totalListCount,
            'data'  => $arrayResults
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/count", name="list_count", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getListCountAction(Portal $portal, Request $request) {

        $count = $this->marketingListRepository->getTotalCount($portal);

        $response = new JsonResponse([
            'data'  => $count
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/folders/breadcrumbs", name="list_folder_breadcrumbs", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getFolderBreadcrumbsAction(Portal $portal, Request $request) {

        $folderId = $request->query->get('folderId', null);

        $payload = $this->folderBreadcrumbs->generate($folderId, $portal);

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{listId}/download", name="download_list", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param MarketingList $list
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function downloadAction(Portal $portal, MarketingList $list) {

        $query = $list->getQuery();
        if($list->getType() === MarketingList::STATIC_LIST) {
            $query = $list->getStaticListQuery();
        }

        $em = $this->entityManager;
        $stmt = $em->getConnection()->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $response = new Response($this->serializer->encode($results, 'csv'));

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$list->getName()}.csv");

        return $response;

    }

    /**
     * @Route("/{listId}/delete-form", name="delete_list_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param MarketingList $list
     * @return JsonResponse
     */
    public function getDeleteListFormAction(Portal $portal, MarketingList $list) {

        $form = $this->createForm(DeleteListType::class, $list);

        $formMarkup = $this->renderView(
            'Api/form/delete_list_form.html.twig',
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
     * @Route("/{listId}/delete", name="delete_list", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param MarketingList $list
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteListAction(Portal $portal, MarketingList $list, Request $request)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::DELETE_LIST,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $form = $this->createForm(DeleteListType::class, $list);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_list_form.html.twig',
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

        // delete report here
        /** @var $list MarketingList */
        $list = $form->getData();
        $this->entityManager->remove($list);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/{listId}", name="get_list", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param MarketingList $list
     * @param Request $request
     * @return JsonResponse
     */
    public function getReportAction(Portal $portal, MarketingList $list, Request $request) {

        $json = $this->serializer->serialize($list, 'json', ['groups' => ['LIST']]);

        $payload = json_decode($json, true);

        return new JsonResponse([
            'success' => true,
            'data'  => $payload['data']
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{internalName}/{listId}/edit-list-save", name="api_edit_list", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param MarketingList $list
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function editListAction(Portal $portal, CustomObject $customObject, MarketingList $list, Request $request) {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::CREATE_LIST,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }
        $data = $request->request->get('data', []);
        $listName = $data['listName'];
        $query = $this->recordRepository->newReportLogicBuilder($data, $customObject, true);
        $list->setQuery($query);
        $list->setCustomObject($customObject);
        $list->setData($data);
        $list->setName($listName);
        $list->setPortal($portal);
        $this->entityManager->persist($list);
        $this->entityManager->flush();
        $response = new JsonResponse([
            'success' => true,
            'listId' => $list->getId()
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/list-preview", name="get_list_preview", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getListPreviewAction(Portal $portal, CustomObject $customObject, Request $request) {

        $data = $request->request->get('data', []);

        $columnOrder = $request->request->get('columnOrder', []);

        $listType = $request->request->get('listType');

        $listId = $request->request->get('listId');

        if($listType === MarketingList::STATIC_LIST && $listId) {

            $list = $this->marketingListRepository->find($listId);

            $query = $list->getStaticListQuery();

            $em = $this->entityManager;
            $stmt = $em->getConnection()->prepare($query);
            $stmt->execute();
            $results = $stmt->fetchAll();

        } else {

            $results = $this->recordRepository->getReportData($data, $customObject, $columnOrder);
            $results = $results['results'];
        }

        $properties = $customObject->getProperties()->toArray();

        foreach($results as &$result) {

            foreach($result as $key => $value) {

                $customObjectProperty = array_filter($properties, function($property) use($key) {
                    $isCustomObjectProperty = $property->getFieldType() === FieldCatalog::CUSTOM_OBJECT;
                    $internalNameMatches = $property->getInternalName() === $key;

                    return $isCustomObjectProperty && $internalNameMatches;
                });

                if(!empty($customObjectProperty)) {
                    $customObjectProperty = array_values($customObjectProperty);

                    if(in_array($value, ['-', ''])) {
                        continue;
                    }

                    $value = json_decode($value);
                    $value = is_array($value) ? $value : [$value];

                    $urls = [];
                    foreach($value as $v) {
                        $url = sprintf("%s/%s",
                            $this->generateUrl('record_list', [
                                'internalIdentifier' => $portal->getInternalIdentifier(),
                                'internalName' => $customObjectProperty[0]->getField()->getCustomObject()->getInternalName()
                            ]),
                            $v
                        );
                        $urls[] = "<a href='$url'>$v</a>";
                    }
                    $result[$key] = implode(',', $urls);
                }

                $choiceFieldProperty = array_filter($properties, function($property) use($key) {
                    $isChoiceFieldProperty = $property->getFieldType() === FieldCatalog::MULTIPLE_CHECKBOX;
                    $internalNameMatches = $property->getInternalName() === $key;

                    return $isChoiceFieldProperty && $internalNameMatches;
                });

                if(!empty($choiceFieldProperty)) {

                    if(in_array($value, ['-', ''])) {
                        continue;
                    }

                    $value = json_decode($value);
                    $value = is_array($value) ? $value : [$value];

                    $items = [];
                    foreach($value as $v) {
                        $items[] = $v;
                    }
                    $result[$key] = implode(',', $items);
                }
            }
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $results
        ], Response::HTTP_OK);

        return $response;

    }

}