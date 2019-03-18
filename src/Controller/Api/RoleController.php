<?php

namespace App\Controller\Api;

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
use App\Form\RoleType;
use App\Model\FieldCatalog;
use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RoleRepository;
use App\Service\MessageGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
 * Class RoleController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/roles")
 *
 */
class RoleController extends ApiController
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
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * RoleController constructor.
     * @param EntityManagerInterface $entityManager
     * @param CustomObjectRepository $customObjectRepository
     * @param PropertyRepository $propertyRepository
     * @param PropertyGroupRepository $propertyGroupRepository
     * @param RoleRepository $roleRepository
     * @param SerializerInterface $serializer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CustomObjectRepository $customObjectRepository,
        PropertyRepository $propertyRepository,
        PropertyGroupRepository $propertyGroupRepository,
        RoleRepository $roleRepository,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->customObjectRepository = $customObjectRepository;
        $this->propertyRepository = $propertyRepository;
        $this->propertyGroupRepository = $propertyGroupRepository;
        $this->roleRepository = $roleRepository;
        $this->serializer = $serializer;
    }


    /**
     * @Route("/get-for-datatable", name="roles_for_datatable", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function getRolesForDatatableAction(Portal $portal, Request $request) {

        $roles = $this->roleRepository->getRolesByPortal($portal);

        $json = $this->serializer->serialize($roles, 'json', ['groups' => ['ROLES_FOR_DATATABLE']]);

        $payload = json_decode($json, true);

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/create-role", name="create_role", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     */
    public function createRoleAction(Portal $portal, Request $request) {

        $role = new Role();
        $role->setPortal($portal);

        $form = $this->createForm(RoleType::class, $role, [
            'portal' => $portal
        ]);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/role_form.html.twig',
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

            /** @var $role Role */
            $role = $form->getData();
            $this->entityManager->persist($role);
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
     * @Route("/{roleId}/edit-role", name="edit_role", methods={"GET", "POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function editRoleAction(Portal $portal, Request $request, Role $role)
    {

        $role->setPortal($portal);

        $form = $this->createForm(RoleType::class, $role, [
            'portal' => $portal
        ]);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/edit_role_form.html.twig',
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

            /** @var $role Role */
            $role = $form->getData();
            $this->entityManager->persist($role);
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
     * @Route("/{roleId}/delete-role", name="delete_role", methods={"GET", "POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param Request $request
     * @param Role $role
     * @return JsonResponse
     */
    public function deleteRoleAction(Portal $portal, Request $request, Role $role)
    {

        $this->entityManager->remove($role);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true
            ],
            Response::HTTP_OK
        );
    }

}