<?php

namespace App\Controller;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Form\CustomObjectType;
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

/**
 * Class PropertySettingsController
 * @package App\Controller
 *
 * @Route("/portal/{portal}")
 *
 */
class PropertySettingsController extends AbstractController
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
     * PropertySettingsController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
    }


    /**
     * @Route("/property-settings/{internalName}", name="property_settings", methods={"GET"}, defaults={"internalName"="contact"})
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Portal $portal, CustomObject $customObject) {

        return $this->render('propertySettings/index.html.twig', array(
            'portal' => $portal,
            'customObject' => $customObject
        ));
    }

    /**
     * @Route("property-settings/get-property-group-form", name="property_group_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @return JsonResponse
     */
    public function getPropertyGroupFormAction(Portal $portal) {

        $propertyGroup = new PropertyGroup();

        $form = $this->createForm(PropertyGroupType::class, $propertyGroup);

        $formMarkup = $this->renderView(
            'Api/form/property_group_form.html.twig',
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
     * @Route("/custom-object/{customObject}/property-settings/create-property-group", name="property_group_new", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function createPropertyGroupAction(Portal $portal, CustomObject $customObject, Request $request)
    {
        $propertyGroup = new PropertyGroup();

        $form = $this->createForm(PropertyGroupType::class, $propertyGroup);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/property_group_form.html.twig',
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

        /** @var $propertyGroup PropertyGroup */
        $propertyGroup = $form->getData();
        $propertyGroup->setCustomObject($customObject);

        $this->entityManager->persist($propertyGroup);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/custom-object/{customObject}/property-settings/create-property", name="create_property", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function createPropertyAction(Portal $portal, CustomObject $customObject, Request $request) {

        $property = new Property();

        $form = $this->createForm(PropertyType::class, $property);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/property_form.html.twig',
            [
                'form' => $form->createView(),
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
            /** @var $property Property */
            $property = $form->getData();
            $property->setCustomObject($customObject);

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
     * @Route("/custom-object/{customObject}/property-settings/get-properties-for-datatable", name="properties_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesForDatatableAction(Portal $portal, CustomObject $customObject, Request $request) {

        $propertyGroups = $this->propertyGroupRepository->getDataTableData($customObject);
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

            foreach($properties as $property) {
                $payload['properties'][$propertyGroupId][] = [
                    'label' => $property->getLabel(),
                    ];
            }
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);

       return $response;
    }

}