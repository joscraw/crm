<?php

namespace App\Controller\Api;

use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Form\CustomObjectType;
use App\Form\EditPropertyGroupType;
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
 * Class PropertyGroupController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/property-groups")
 *
 */
class PropertyGroupController extends ApiController
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
     * @Route("/get-create-form", name="property_group_form", methods={"GET"}, options = { "expose" = true })
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
     * @Route("{propertyGroup}/get-edit-form", name="edit_property_group_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param PropertyGroup $propertyGroup
     * @return JsonResponse
     */
    public function getEditPropertyGroupFormAction(Portal $portal, PropertyGroup $propertyGroup) {

        $form = $this->createForm(EditPropertyGroupType::class, $propertyGroup);

        $formMarkup = $this->renderView(
            'Api/form/edit_property_group_form.html.twig',
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
     * @Route("{propertyGroup}/edit", name="edit_property_group", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @param PropertyGroup $propertyGroup
     * @return JsonResponse
     */
    public function editPropertyGroupAction(Portal $portal, Request $request, PropertyGroup $propertyGroup)
    {

        $form = $this->createForm(EditPropertyGroupType::class, $propertyGroup);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/edit_property_group_form.html.twig',
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

        /** @var $customObject CustomObject */
        $propertyGroup = $form->getData();
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
     * @Route("/create", name="property_group_new", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Controller\Exception\InvalidInputException
     * @throws \App\Controller\Exception\MissingRequiredQueryParameterException
     */
    public function createPropertyGroupAction(Portal $portal, CustomObject $customObject, Request $request)
    {
        $customObject = $this->getCustomObjectForRequest($this->customObjectRepository);

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

}