<?php

namespace App\Controller\Api;

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
     * @Route("/create", name="create_property", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Controller\Exception\InvalidInputException
     * @throws \App\Controller\Exception\MissingRequiredQueryParameterException
     */
    public function createPropertyAction(Portal $portal, CustomObject $customObject, Request $request) {

        $customObject = $this->getCustomObjectForRequest($this->customObjectRepository);

        $property = new Property();

        $form = $this->createForm(PropertyType::class, $property);

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
     * @Route("/get-for-datatable", name="properties_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Controller\Exception\InvalidInputException
     * @throws \App\Controller\Exception\MissingRequiredQueryParameterException
     */
    public function getPropertiesForDatatableAction(Portal $portal, Request $request) {

        $customObject = $this->getCustomObjectForRequest($this->customObjectRepository);

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