<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Role;
use App\Form\CustomObjectType;
use App\Form\DeletePropertyType;
use App\Form\EditPropertyType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Service\MessageGenerator;
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
use Symfony\Component\Validator\Constraints\GroupSequence;


/**
 * Class PropertyController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/properties")
 *
 */
class PropertyController extends ApiController
{
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
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * PropertyController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param SerializerInterface $serializer
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        SerializerInterface $serializer,
        PermissionAuthorizationHandler $permissionAuthorizationHandler
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->serializer = $serializer;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
    }

    /**
     * @Route("{internalName}/create", name="create_property", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function createPropertyAction(Portal $portal, CustomObject $customObject, Request $request) {

        $property = new Property();
        $property->setCustomObject($customObject);

        $skipValidation = $request->request->get('skip_validation', false);

        $options = [
            'portal' => $portal,
            'customObject' => $customObject
        ];

        if(!$skipValidation) {
            $options['validation_groups'] = ['CREATE'];
        }

        $form = $this->createForm(PropertyType::class, $property, $options);

        $form->handleRequest($request);

        $fieldHelpMessage = FieldCatalog::getOptionsForFieldType(FieldCatalog::SINGLE_LINE_TEXT)['description'];
        if($property->getFieldType()) {
            $fieldHelpMessage = FieldCatalog::getOptionsForFieldType($property->getFieldType())['description'];
        }

        $formMarkup = $this->renderView(
            'Api/form/property_form.html.twig',
            [
                'form' => $form->createView(),
                'fieldHelpMessage' => $fieldHelpMessage
            ]
        );

        if($form->isSubmitted()) {

            $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
                $this->getUser(),
                Role::CREATE_PROPERTY,
                Role::SYSTEM_PERMISSION
            );

            if(!$hasPermission) {
                return new JsonResponse(
                    [
                        'success' => false,
                    ], Response::HTTP_UNAUTHORIZED
                );
            }

        }

        if ($form->isSubmitted() && !$form->isValid()) {

            if(!$form->isValid()) {
                return new JsonResponse(
                    [
                        'success' => false,
                        'formMarkup' => $formMarkup,
                    ], Response::HTTP_BAD_REQUEST
                );
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $property Property */
            $property = $form->getData();
            $this->entityManager->persist($property);
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
     * @Route("/{internalName}/{propertyInternalName}/edit", name="edit_property", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Property $property
     * @param Request $request
     * @return JsonResponse
     */
    public function editPropertyAction(Portal $portal, CustomObject $customObject, Property $property, Request $request) {

        $property->setCustomObject($customObject);

        $skipValidation = $request->request->get('skip_validation', false);

        $options = [
            'portal' => $portal,
            'customObject' => $customObject,
            'property' => $property
        ];

        if(!$skipValidation) {
            $options['validation_groups'] = new GroupSequence(['FIRST', 'EDIT']);
        }

        $form = $this->createForm(EditPropertyType::class, $property, $options);

        $form->handleRequest($request);

        $fieldHelpMessage = FieldCatalog::getOptionsForFieldType(FieldCatalog::SINGLE_LINE_TEXT)['description'];
        if($property->getFieldType()) {
            $fieldHelpMessage = FieldCatalog::getOptionsForFieldType($property->getFieldType())['description'];
        }

        $formMarkup = $this->renderView(
            'Api/form/property_form.html.twig',
            [
                'form' => $form->createView(),
                'fieldHelpMessage' => $fieldHelpMessage
            ]
        );

        if($form->isSubmitted()) {

            $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
                $this->getUser(),
                Role::EDIT_PROPERTY,
                Role::SYSTEM_PERMISSION
            );

            if(!$hasPermission) {
                return new JsonResponse(
                    [
                        'success' => false,
                    ], Response::HTTP_UNAUTHORIZED
                );
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {

            if(!$form->isValid()) {
                return new JsonResponse(
                    [
                        'success' => false,
                        'formMarkup' => $formMarkup,
                    ], Response::HTTP_BAD_REQUEST
                );
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $property Property */
            $property = $form->getData();
            $this->entityManager->persist($property);
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
     * @Route("/{internalName}/get-for-datatable", name="properties_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesForDatatableAction(Portal $portal, CustomObject $customObject, Request $request) {

        $propertyGroups = $this->propertyGroupRepository->getPropertyGroupsAndProperties($customObject);
        $payload = [];
        $payload['property_groups'] = [];
        $payload['properties']= [];

        foreach($propertyGroups as $propertyGroup) {
            $propertyGroupId = $propertyGroup->getId();
            $payload['property_groups'][$propertyGroupId] = [
                'id' => $propertyGroupId,
                'label' => $propertyGroup->getName(),
                'internalName' => $propertyGroup->getInternalName()
            ];

            $properties = $propertyGroup->getProperties();

            foreach($properties as $property) {
                $payload['properties'][$propertyGroupId][] = [
                    'label' => $property->getLabel(),
                    'internalName' => $property->getInternalName(),
                    'id' => $property->getId()
                ];
            }
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/get-for-columns", name="properties_for_columns", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesForColumnsAction(Portal $portal, CustomObject $customObject, Request $request) {

        $propertyGroups = $this->propertyGroupRepository->getPropertiesForCustomObject($customObject);
        $payload = [];
        $payload['property_groups'] = [];
        $payload['properties']= [];

        foreach($propertyGroups as $propertyGroup) {
            $propertyGroupId = $propertyGroup->getId();
            $payload['property_groups'][$propertyGroupId] = [
                'id' => $propertyGroupId,
                'label' => $propertyGroup->getName()
            ];

            $properties = $propertyGroup->getProperties();

            $payload['properties'][$propertyGroupId] = [];
            foreach($properties as $property) {
                $payload['properties'][$propertyGroupId][] = [
                    'id' => $property->getId(),
                    'label' => $property->getLabel(),
                    'isColumn' => $property->getIsColumn(),
                    'columnOrder' => $property->getColumnOrder()
                ];
            }
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}", name="get_properties", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesAction(Portal $portal, CustomObject $customObject, Request $request) {
        $excludeCustomObjects = $request->query->get('excludeCustomObjects', false);
        $includeGroupingLabel = $request->query->get('includeGroupingLabel', false);
        $propertyGroups = $this->propertyGroupRepository->getPropertyGroupsAndProperties($customObject);
        if($excludeCustomObjects) {
            foreach($propertyGroups as $propertyGroup) {
                /** @var PropertyGroup $propertyGroup */
                $properties = $propertyGroup->getProperties()->filter(function(Property $property) {
                    return $property->getFieldType() !== FieldCatalog::CUSTOM_OBJECT;

                });
                $propertyGroup->setProperties($properties);
            }
        }
        $payload['property_groups'] = [];
        $json = $this->serializer->serialize($propertyGroups, 'json', ['groups' => ['SELECTABLE_PROPERTIES']]);
        $propertyGroups = json_decode($json, true);
        if($includeGroupingLabel) {
            foreach($propertyGroups as $propertyGroup) {
                $propertyGroup['grouping_label'] = sprintf("%s - %s", $customObject->getLabel(), $propertyGroup['name']);
            }
        }
        $payload['property_groups'] = $propertyGroups;
        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/{internalName}/get-from-objects", name="get_properties_from_multiple_objects", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getPropertiesFromMultipleObjectsAction(Portal $portal, CustomObject $customObject, Request $request) {
        /**
         * An array of all possible available properties. NO Duplicates though!
         * @var array $availableProperties
         */
        $availableProperties = [];
        /**
         * To see the JSON structure being used in reports see the below file
         * https://jsoncompare.com/#!/simple/id=7b48824006f64ac81539d6f44b0c9fdd/
         * @var array $data
         */
        $data = $request->request->get('data');
        $uids = [];
        if(!empty($data['joins'])) {
            foreach($data['joins'] as $join) {
                if(!empty($join['connected_object']['join_direction']) &&  $join['connected_object']['join_direction'] === 'normal_join') {
                    $uids[] = $join['connected_object']['id'];
                    $uids[] = $join['connected_property']['field']['customObject']['id'];
                } else if(!empty($join['connected_object']['join_direction']) &&  $join['connected_object']['join_direction'] === 'cross_join') {
                    $uids[] = $customObject->getId();
                    $uids[] = $join['connected_object']['id'];
                } else {
                    $uids[] = $customObject->getId();
                }
            }
        } else {
            $uids[] = $customObject->getId();
        }
        $availableProperties = $this->propertyRepository->getForReport(array_unique($uids));
        // decode the nested JSON FIELD
        for($i = 0; $i < count($availableProperties); $i++) {
            $availableProperties[$i]['field'] = json_decode($availableProperties[$i]['field'], true);
        }
        $payload['property_groups'] = [];
        for($i = 0; $i < count($availableProperties); $i++) {
            $availableProperty = $availableProperties[$i];
            $payload['property_groups'][$availableProperty['property_group_id']]['id'] = $availableProperty['property_group_id'];
            $payload['property_groups'][$availableProperty['property_group_id']]['name'] = $availableProperty['property_group_name'];
            $payload['property_groups'][$availableProperty['property_group_id']]['grouping_label'] = $availableProperty['grouping_label'];
            $payload['property_groups'][$availableProperty['property_group_id']]['properties'][] = $availableProperty;
        }
        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }

    /**
     * @Route("/object/{internalName}/connectable", name="get_connectable_properties", methods={"GET"}, options = { "expose" = true })
     * @param CustomObject $customObject
     * @param Portal $portal
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getConnectablePropertiesAction(CustomObject $customObject, Portal $portal, Request $request) {
        $customObjectId = $request->query->get('customObjectId');
        $connectableCustomObject = $this->customObjectRepository->find($customObjectId);
        $properties = $this->propertyRepository->getConnectableProperties($customObject, $connectableCustomObject);
        // decode the nested JSON FIELD
        for($i = 0; $i < count($properties); $i++) {
            $properties[$i]['field'] = json_decode($properties[$i]['field'], true);
        }
        $payload = [];
        $payload['properties'] = $properties;
        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ],  Response::HTTP_OK);
        return $response;
    }

    /**
     * @Route("/{internalName}/get-default-properties", name="get_default_properties", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getDefaultPropertiesAction(Portal $portal, CustomObject $customObject, Request $request) {

        $propertyGroups = $this->propertyGroupRepository->getDefaultPropertyData($customObject);
        $payload = [];
        $payload['property_groups'] = [];
        $payload['properties']= [];

        foreach($propertyGroups as $propertyGroup) {
            $propertyGroupId = $propertyGroup->getId();
            $payload['property_groups'][$propertyGroupId] = [
                'id' => $propertyGroupId,
                'label' => $propertyGroup->getName()
            ];

            $properties = $propertyGroup->getProperties();

            $payload['properties'][$propertyGroupId] = [];
            foreach($properties as $property) {
                $payload['properties'][$propertyGroupId][] = [
                    'id' => $property->getId(),
                    'label' => $property->getLabel(),
                    'isDefaultProperty' => $property->getIsDefaultProperty(),
                    'propertyOrder' => $property->getDefaultPropertyOrder()
                ];
            }
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/set-columns", name="set_property_columns", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function setColumnsAction(Portal $portal, CustomObject $customObject, Request $request) {

        $selectedProperties = $request->request->get('selected_properties', []);
        $allProperties = $this->propertyRepository->findByCustomObject($customObject);

        foreach($allProperties as $property) {
            $key = array_search($property->getId(), $selectedProperties);

            if($key !== false) {
                $property->setIsColumn(true);
                $property->setColumnOrder($key);
            } else {
                $property->setIsColumn(false);
                $property->setColumnOrder(null);
            }

            $this->entityManager->persist($property);
            $this->entityManager->flush();
        }

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/set-default-properties", name="set_default_properties", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function setDefaultPropertiesAction(Portal $portal, CustomObject $customObject, Request $request) {

        $selectedProperties = $request->request->get('selected_properties', []);
        $allProperties = $this->propertyRepository->findByCustomObject($customObject);

        foreach($allProperties as $property) {
            $key = array_search($property->getId(), $selectedProperties);

            if($key !== false) {
                $property->setIsDefaultProperty(true);
                $property->setDefaultPropertyOrder($key);
            } else {
                $property->setIsDefaultProperty(false);
                $property->setDefaultPropertyOrder(null);
            }

            $this->entityManager->persist($property);
            $this->entityManager->flush();
        }

        $response = new JsonResponse([
            'success' => true
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/get-columns-for-datatable", name="get_columns_for_table", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getColumnsForDataTableAction(Portal $portal, CustomObject $customObject, Request $request) {

        $properties = $this->propertyRepository->findColumnsForTable($customObject);


        $payload = [];
        foreach($properties as $property) {
            $payload[]= [
                'data' => $property->getInternalName(),
                'name' => $property->getInternalName(),
                'title' => $property->getLabel(),
            ];
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalName}/{propertyInternalName}/delete-form", name="delete_property_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Property $property
     * @param Request $request
     * @return JsonResponse
     */
    public function getDeletePropertyFormAction(Portal $portal, CustomObject $customObject, Property $property, Request $request) {

        $form = $this->createForm(DeletePropertyType::class, $property);

        $formMarkup = $this->renderView(
            'Api/form/delete_property_form.html.twig',
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
     * @Route("/{internalName}/{propertyInternalName}/delete", name="delete_property", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @param Property $property
     * @return JsonResponse
     */
    public function deletePropertyAction(Portal $portal, Request $request, Property $property)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::DELETE_PROPERTY,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $form = $this->createForm(DeletePropertyType::class, $property);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_property_form.html.twig',
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

        // delete custom object here
        /** @var $property Property */
        $property = $form->getData();
        $this->entityManager->remove($property);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }
}