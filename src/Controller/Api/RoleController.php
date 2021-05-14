<?php

namespace App\Controller\Api;

use App\Entity\Portal;
use App\Entity\Role;
use App\Form\RoleType;
use App\Utils\ServiceHelper;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class RoleController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/roles")
 *
 */
class RoleController extends ApiController
{
    use ServiceHelper;

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

        if($form->isSubmitted()) {

            $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
                $this->getUser(),
                Role::CREATE_ROLE,
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

        if($form->isSubmitted()) {

            $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
                $this->getUser(),
                Role::EDIT_ROLE,
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

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::DELETE_ROLE,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

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