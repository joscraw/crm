<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Filter;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Form\BulkEditType;
use App\Form\CustomObjectType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RecordType;
use App\Form\SaveFilterType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\FilterRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RecordRepository;
use App\Service\MessageGenerator;
use App\Utils\ArrayHelper;
use App\Utils\MultiDimensionalArrayExtractor;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
 * Class RecordController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/records")
 */
class RecordController extends ApiController
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
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * @var FilterRepository
     */
    private $filterRepository;

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
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RecordRepository $recordRepository,
        SerializerInterface $serializer,
        PermissionAuthorizationHandler $permissionAuthorizationHandler,
        FilterRepository $filterRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->recordRepository = $recordRepository;
        $this->serializer = $serializer;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
        $this->filterRepository = $filterRepository;
    }

    /**
     * @Route("/{internalName}/create-form", name="create_record_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function getRecordFormAction(Portal $portal, CustomObject $customObject) {

        $properties = $this->propertyRepository->findDefaultProperties($customObject);

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
     * @Route("/{internalName}/bulk-edit", name="bulk_edit", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkEditAction(Portal $portal, CustomObject $customObject, Request $request) {

        $form = $this->createForm(BulkEditType::class, null, [
            'customObject' => $customObject
        ]);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/bulk_edit_form.html.twig',
            [
                'form' => $form->createView(),
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

            $propertyToUpdate = $form->get('propertyToUpdate')->getData();
            $propertyToUpdate = $this->propertyRepository->find($propertyToUpdate);

            $records = $request->request->get('records', []);

            foreach($records as $record) {

                $record = $this->recordRepository->find($record);

                $properties = $record->getProperties();
                $properties[$propertyToUpdate->getInternalName()] = $form->get('propertyValue')->getData();
                $record->setProperties($properties);

                $this->entityManager->persist($record);
                $this->entityManager->flush();

            }

        }


        return new JsonResponse(
            [
                'success' => true,
                'formMarkup' => $formMarkup
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{internalName}/{recordId}/edit-form", name="edit_record_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Record $record
     * @return JsonResponse
     */
    public function getEditRecordFormAction(Portal $portal, CustomObject $customObject, Record $record) {

        $properties = $this->propertyRepository->findBy([
            'customObject' => $customObject->getId()
        ]);

        $recordProperties = $record->getProperties();

        $form = $this->createForm(RecordType::class, $recordProperties, [
            'properties' => $properties,
            'portal' => $portal
        ]);

        $propertyGroups = $customObject->getPropertyGroups();

        $formMarkup = $this->renderView(
            'Api/form/edit_record_form.html.twig',
            [
                'form' => $form->createView(),
                'propertyGroups' => $propertyGroups
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
     * @Route("/{internalName}/{recordId}/edit", name="edit_record", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Record $record
     * @param Request $request
     * @return JsonResponse
     */
    public function editRecordAction(Portal $portal, CustomObject $customObject, Record $record, Request $request) {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            sprintf('EDIT_%s', $customObject->getFormatForRole())
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $properties = $this->propertyRepository->findBy([
            'customObject' => $customObject->getId()
        ]);

        $recordProperties = $record->getProperties();

        $form = $this->createForm(RecordType::class, $recordProperties, [
            'properties' => $properties,
            'portal' => $portal
        ]);

        $propertyGroups = $customObject->getPropertyGroups();

        $formFieldMap = [];
        foreach($propertyGroups as $propertyGroup) {
            $formFieldMap[$propertyGroup->getInternalName()] = [];
            $internalNames = $this->propertyRepository->findAllInternalNamesForPropertiesByPropertyGroup($propertyGroup);
            $internalNames = $this->getArrayValuesRecursive($internalNames);
            $formFieldMap[$propertyGroup->getInternalName()] = $internalNames;
        }

        $form->handleRequest($request);

        if (!$form->isValid()) {

            $formMarkup = $this->renderView(
                'Api/form/edit_record_form.html.twig',
                [
                    'form' => $form->createView(),
                    'formFieldMap' => $formFieldMap,
                    'propertyGroups' => $propertyGroups
                ]
            );

            return new JsonResponse(
                [
                    'success' => false,
                    'formMarkup' => $formMarkup,
                ], Response::HTTP_BAD_REQUEST
            );
        }

        $record->setProperties($form->getData());
        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{internalName}/create", name="create_record", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function createRecordAction(Portal $portal, CustomObject $customObject, Request $request) {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            sprintf('CREATE_%s', $customObject->getFormatForRole())
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $properties = $this->propertyRepository->findDefaultProperties($customObject);

        $form = $this->createForm(RecordType::class, null, [
            'properties' => $properties,
            'portal' => $portal
        ]);

        $form->handleRequest($request);

        if (!$form->isValid()) {

            $formMarkup = $this->renderView(
                'Api/form/record_form.html.twig',
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

        $record = new Record();
        $record->setProperties($form->getData());
        $record->setCustomObject($customObject);

        $this->entityManager->persist($record);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }




    /**
     * @Route("/{internalName}/selectize", name="records_for_selectize", methods={"GET"}, options = { "expose" = true })
     * @see https://stackoverflow.com/questions/29444430/remote-data-loading-from-sql-with-selectize-js
     * @see https://selectize.github.io/selectize.js/
     * @param Request $request
     * @param CustomObject $customObject
     * @return Response
     * @throws \App\Controller\Exception\InvalidInputException
     * @throws \App\Controller\Exception\MissingRequiredQueryParameterException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRecordsForSelectizeAction(Request $request, CustomObject $customObject) {

        $search = $request->query->get('search');
        $property = $this->getPropertyForRequest($this->propertyRepository);
        $allowedCustomObjectToSearch = $property->getField()->getCustomObject();
        $selectizeAllowedSearchableProperties = $property->getField()->getSelectizeSearchResultProperties();

        $results = $this->recordRepository->getSelectizeData($search, $allowedCustomObjectToSearch, $selectizeAllowedSearchableProperties);
        $internalNameToLabelMap = $this->propertyRepository->findAllInternalNamesAndLabelsForCustomObject($allowedCustomObjectToSearch);

        $selectizeRecords = [];
        foreach($results as $result) {
            $properties = $result['properties'];
            $selectizeRecord = [];
            $selectizeRecord['valueField'] = $result['id'];

            $labels = [];
            foreach($result as $internalName => $value) {
                $key = array_search($internalName, array_column($internalNameToLabelMap, 'internalName'));
                if($key !== false) {
                    $label = $internalNameToLabelMap[$key]['label'];
                } elseif($internalName === 'id') {
                    $label = 'Id';
                } else {
                    continue;
                }

                $labels[] = sprintf("%s: %s", $label, $value);
            }

            $selectizeRecord['labelField'] = implode(', ', $labels);

            $selectizeRecord['searchField'] = 'id:' . $result['id'] . ' ' . json_encode($properties);
            $selectizeRecords[] = $selectizeRecord;
        }

        $response = new JsonResponse($selectizeRecords,  Response::HTTP_OK);
        return $response;
    }

    /**
     * DataTables passes unique params in the Request and expects a specific response payload
     * @see https://datatables.net/manual/server-side Documentation for ServerSide Implimentation for DataTables
     *
     * @Route("/{internalName}/datatable", name="records_for_datatable", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRecordsForDatatableAction(Portal $portal, CustomObject $customObject, Request $request) {

        $draw = intval($request->request->get('draw'));
        $start = $request->request->get('start');
        $length = $request->request->get('length');
        $search = $request->request->get('search');
        $orders = $request->request->get('order');
        $columns = $request->request->get('columns');
        $customFilters = $request->request->get('customFilters', []);

        $propertiesForDatatable = $this->propertyRepository->findColumnsForTable($customObject);

        $results = $this->recordRepository->getDataTableData($start, $length, $search, $orders, $columns, $propertiesForDatatable, $customFilters, $customObject);

        $customObjectInternalNames = $this->propertyRepository->findAllInternalNamesByFieldTypeForCustomObject($customObject, FieldCatalog::CUSTOM_OBJECT);
        $customObjectInternalNames = $this->getArrayValuesRecursive($customObjectInternalNames);

        $properties = $customObject->getProperties()->toArray();

        foreach($results['results'] as &$result) {

            foreach($result as $key => $value) {

                $customObjectProperty = array_filter($properties, function($property) use($key) {
                    $isCustomObjectProperty = $property->getFieldType() === FieldCatalog::CUSTOM_OBJECT;
                    $internalNameMatches = $property->getInternalName() === $key;

                    return $isCustomObjectProperty && $internalNameMatches;
                });

                if(!empty($customObjectProperty)) {

                    // We need to reset the array keys to start at 0 after using array_filter
                    $customObjectProperty = array_values($customObjectProperty);

                    $values = explode(";", $value);

                    $urls = [];
                    foreach($values as $v) {
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

                    $values = explode(";", $value);

                    $result[$key] = implode(',', $values);
                }
            }
        }

        $countQuery = $this->recordRepository->findCountByCustomObject($customObject);
        $totalRecordsCount = !empty($countQuery[0]['count']) ? $countQuery[0]['count'] : 0;

        $results = $results['results'];
        $filteredRecordsCount = count($results);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) || !empty($customFilters) ? $filteredRecordsCount : $totalRecordsCount,
            'recordsTotal'  => $totalRecordsCount,
            'data'  => $results
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/save-filter-form", name="save_filter_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function getSaveFilterFormAction(Portal $portal, CustomObject $customObject) {

        $filter = new Filter();

        $form = $this->createForm(SaveFilterType::class, $filter);

        $formMarkup = $this->renderView(
            'Api/form/save_filter_form.html.twig',
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
     * @Route("/{internalName}/saved-filters", name="saved_filters", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function getSavedFiltersAction(Portal $portal, CustomObject $customObject) {

        $filters = $this->filterRepository->findBy([
            'portal' => $portal->getId(),
            'type' => Filter::RECORD_FILTER
        ]);


        $json = $this->serializer->serialize($filters, 'json', ['groups' => ['SAVED_FILTERS']]);

        $payload = json_decode($json, true);

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{internalName}/save-filter", name="save_filter", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function saveFilterAction(Portal $portal, CustomObject $customObject, Request $request)
    {

        $customFilters = $request->request->get('customFilters', []);
        $propertiesForDatatable = $this->propertyRepository->findColumnsForTable($customObject);

        $filter = new Filter();
        $filter->setPortal($portal);
        $filter->setType(Filter::RECORD_FILTER);

        $form = $this->createForm(SaveFilterType::class, $filter);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/save_filter_form.html.twig',
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
        /** @var $filter Filter */
        $filter = $form->getData();

        $query = $this->recordRepository->getCustomFiltersMysqlOnly($propertiesForDatatable, $customFilters, $customObject);

        $filter->setQuery($query);
        $filter->setCustomFilters($customFilters);

        $this->entityManager->persist($filter);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );

    }

    /**
     * @Route("/{internalName}/{filterId}/remove-filter", name="remove_filter", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Filter $filter
     * @param Request $request
     * @return JsonResponse
     */
    public function removeFilterAction(Portal $portal, CustomObject $customObject, Filter $filter, Request $request)
    {

        $this->entityManager->remove($filter);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );

    }
}