<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Folder;
use App\Entity\Form;
use App\Entity\MarketingList;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Entity\Report;
use App\Entity\Role;
use App\Form\CustomObjectType;
use App\Form\DeleteListType;
use App\Form\DeleteReportType;
use App\Form\FolderType;
use App\Form\MoveListToFolderType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RecordType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\FolderRepository;
use App\Repository\MarketingListRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Repository\ReportRepository;
use App\Service\MessageGenerator;
use App\Utils\ArrayHelper;
use App\Utils\ListFolderBreadcrumbs;
use App\Utils\MultiDimensionalArrayExtractor;
use App\Utils\RandomStringGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class FormController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/forms")
 */
class FormController extends ApiController
{
    use MultiDimensionalArrayExtractor;
    use ArrayHelper;
    use RandomStringGenerator;

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
     * @Route("/initialize", name="initialize_form", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function initializeFormAction(Portal $portal, Request $request) {

        $customObjectId = $request->request->get('customObjectId', null);
        $customObject = $this->customObjectRepository->find($customObjectId);
        $form = new Form();
        $form->setType(Form::REGULAR_FORM);
        $form->setPortal($portal);
        $form->setCustomObject($customObject);
        $form->setUid($this->generateRandomString(40));
        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $json = $this->serializer->serialize($form, 'json', ['groups' => ['FORMS']]);

        $payload = json_decode($json, true);

        return new JsonResponse(
            [
                'success' => true,
                'data' => $payload,
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/{uid}", name="get_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function getFormAction(Portal $portal, Form $form, Request $request) {

        $json = $this->serializer->serialize($form, 'json', ['groups' => ['FORMS']]);

        $payload = json_decode($json, true);

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{uid}/save-form", name="save_form", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Form $form
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFormAction(Portal $portal, Form $form, Request $request) {

        $data = $request->request->get('data', []);

        $form->setData($data);
        $this->entityManager->persist($form);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;

    }

    /**
     * @Route("/{uid}/form-preview", name="form_preview", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getFormPreviewAction(Portal $portal, CustomObject $customObject, Request $request) {

        $data = $request->request->get('data', []);

        $propertyIds = [];
        foreach($data as $uid => $property) {
            $propertyIds[] = $property['id'];
        }

        $properties = $this->propertyRepository->findBy( array('id' => $propertyIds));

        $form = $this->createForm(RecordType::class, null, [
            'properties' => $properties,
            'portal' => $portal
        ]);

        $formMarkup = $this->renderView(
            'Api/form/record_form.html.twig',
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

        $listName = $request->request->get('listName');

        $listType = $request->request->get('listType');

        $columnOrder = $request->request->get('columnOrder', []);

        $query = $this->recordRepository->getReportMysqlOnly($data, $customObject, $columnOrder);

        $list = new MarketingList();

        $list->setQuery($query);
        $list->setCustomObject($customObject);
        $list->setData($data);
        $list->setName($listName);
        $list->setPortal($portal);
        $list->setColumnOrder($columnOrder);
        $list->setType($listType);


        if($listType = MarketingList::STATIC_LIST) {

            $results = $this->recordRepository->getReportRecordIds($data, $customObject);

            $records = $results['results'];

            $list->setRecords($records);


        }

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'listId' => $list->getId(),
            'success' => true
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
            'data'  => $payload
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
            Role::EDIT_LIST,
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

        $listName = $request->request->get('listName');

        $listType = $request->request->get('listType');

        $columnOrder = $request->request->get('columnOrder', []);

        $query = $this->recordRepository->getReportMysqlOnly($data, $customObject, $columnOrder);

        if($listType = MarketingList::STATIC_LIST) {

            $results = $this->recordRepository->getReportRecordIds($data, $customObject);

            $records = $results['results'];

            $list->setRecords($records);

        }

        $list->setQuery($query);
        $list->setCustomObject($customObject);
        $list->setData($data);
        $list->setName($listName);
        $list->setPortal($portal);
        $list->setColumnOrder($columnOrder);
        $list->setType($listType);

        $this->entityManager->persist($list);
        $this->entityManager->flush();

        $response = new JsonResponse([
            'success' => true
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