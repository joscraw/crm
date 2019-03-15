<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Role;
use App\Entity\User;
use App\Form\CustomObjectType;
use App\Form\DeletePropertyType;
use App\Form\EditPropertyType;
use App\Form\PropertyGroupType;
use App\Form\PropertyType;
use App\Form\RoleType;
use App\Form\UserType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use App\Service\MessageGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;


/**
 * Class UserController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/users")
 *
 */
class UserController extends ApiController
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
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param UserRepository $userRepository
     * @param SerializerInterface $serializer
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        UserRepository $userRepository,
        SerializerInterface $serializer,
        UserPasswordEncoderInterface $passwordEncoder
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->userRepository = $userRepository;
        $this->serializer = $serializer;
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * @Route("/create", name="create_user", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function createUserAction(Portal $portal, Request $request) {

        $user = new User();

        $form = $this->createForm(UserType::class, $user);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/user_form.html.twig',
            [
                'form' => $form->createView()
            ]
        );

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
            /** @var $user User */
            $user = $form->getData();
            $user->setPortal($portal);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $this->entityManager->persist($user);
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
     * @Route("/get-for-datatable", name="users_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function getUsersForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->query->get('draw'));
        $start = $request->query->get('start');
        $length = $request->query->get('length');
        $search = $request->query->get('search');
        $orders = $request->query->get('order');
        $columns = $request->query->get('columns');

        $results = $this->userRepository->getDataTableData($portal, $start, $length, $search, $orders, $columns);

        $totalReportCount = $this->userRepository->getTotalCount($portal);
        $arrayResults = $results['arrayResults'];
        $filteredReportCount = count($arrayResults);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) ? $filteredReportCount : $totalReportCount,
            'recordsTotal'  => $totalReportCount,
            'data'  => $arrayResults
        ],  Response::HTTP_OK);

        return $response;

    }

    /**
     * @Route("/{internalName}/get-for-filter", name="properties_for_filter", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesForFilter(Portal $portal, CustomObject $customObject, Request $request) {

        $propertyGroups = $this->propertyGroupRepository->getPropertyGroupsAndProperties($customObject);

        $payload['property_groups'] = [];
        foreach($propertyGroups as $propertyGroup) {
            $json = $this->serializer->serialize($propertyGroup, 'json', ['groups' => ['PROPERTIES_FOR_FILTER']]);
            $payload['property_groups'][] = json_decode($json, true);
        }

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
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
     * @Route("/{internalName}/get-for-report", name="properties_for_report", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesForReportAction(Portal $portal, CustomObject $customObject, Request $request) {

        $propertyGroups = $this->propertyGroupRepository->getPropertyGroupsAndProperties($customObject);

        $payload['property_groups'] = [];
        foreach($propertyGroups as $propertyGroup) {
            $json = $this->serializer->serialize($propertyGroup, 'json', ['groups' => ['PROPERTIES_FOR_REPORT']]);
            $payload['property_groups'][] = json_decode($json, true);
        }

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
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