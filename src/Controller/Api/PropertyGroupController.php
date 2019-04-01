<?php

namespace App\Controller\Api;

use App\AuthorizationHandler\PermissionAuthorizationHandler;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\PropertyGroup;
use App\Entity\Role;
use App\Form\DeletePropertyGroupType;
use App\Form\EditPropertyGroupType;
use App\Form\PropertyGroupType;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


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
     * @var PermissionAuthorizationHandler
     */
    private $permissionAuthorizationHandler;

    /**
     * PropertyGroupController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param PermissionAuthorizationHandler $permissionAuthorizationHandler
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        PermissionAuthorizationHandler $permissionAuthorizationHandler
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->permissionAuthorizationHandler = $permissionAuthorizationHandler;
    }

    /**
     * @Route("/{internalName}/get-create-form", name="property_group_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @return JsonResponse
     */
    public function getPropertyGroupFormAction(Portal $portal, CustomObject $customObject) {

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
     * @Route("/{internalName}/{propertyGroupInternalName}/get-edit-form", name="edit_property_group_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param PropertyGroup $propertyGroup
     * @return JsonResponse
     */
    public function getEditPropertyGroupFormAction(Portal $portal, CustomObject $customObject, PropertyGroup $propertyGroup) {

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
     * @Route("/{internalName}/{propertyGroupInternalName}/delete-form", name="delete_property_group_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param PropertyGroup $propertyGroup
     * @param Request $request
     * @return JsonResponse
     */
    public function getDeletePropertyGroupFormAction(Portal $portal, CustomObject $customObject, PropertyGroup $propertyGroup, Request $request) {

        $form = $this->createForm(DeletePropertyGroupType::class, $propertyGroup);

        $formMarkup = $this->renderView(
            'Api/form/delete_property_group_form.html.twig',
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
     * @Route("/{internalName}/{propertyGroupInternalName}/delete", name="delete_property_group", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param PropertyGroup $propertyGroup
     * @param Request $request
     * @return JsonResponse
     */
    public function deletePropertyGroupAction(Portal $portal, CustomObject $customObject, PropertyGroup $propertyGroup, Request $request)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::DELETE_PROPERTY_GROUP,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $form = $this->createForm(DeletePropertyGroupType::class, $propertyGroup);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_property_group_form.html.twig',
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

        // delete property group here
        /** @var $propertyGroup PropertyGroup */
        $propertyGroup = $form->getData();
        $this->entityManager->remove($propertyGroup);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/{internalName}/{propertyGroupInternalName}/edit", name="edit_property_group", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @param PropertyGroup $propertyGroup
     * @return JsonResponse
     */
    public function editPropertyGroupAction(Portal $portal, CustomObject $customObject, PropertyGroup $propertyGroup, Request $request)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::EDIT_PROPERTY_GROUP,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $propertyGroup->setCustomObject($customObject);

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
     * @Route("{internalName}/create", name="property_group_new", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function createPropertyGroupAction(Portal $portal, CustomObject $customObject, Request $request)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::CREATE_PROPERTY_GROUP,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $propertyGroup = new PropertyGroup();

        $propertyGroup->setCustomObject($customObject);

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