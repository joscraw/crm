<?php

namespace App\Controller\Api;

use App\Entity\Portal;
use App\Entity\Role;
use App\Entity\User;
use App\Form\DeleteUserType;
use App\Form\EditUserType;
use App\Form\UserType;
use App\Model\FieldCatalog;
use App\Utils\ServiceHelper;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserController
 * @package App\Controller\Api
 *
 * @Route("{internalIdentifier}/api/users")
 *
 */
class UserController extends ApiController
{
    use ServiceHelper;

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

        if($form->isSubmitted()) {

            $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
                $this->getUser(),
                Role::CREATE_USER,
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
            /** @var $user User */
            $user = $form->getData();
            $user->setPortal($portal);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            ));
            $user->setRoles([User::ROLE_ADMIN_USER]);
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
     * @Route("/{userId}/edit", name="edit_user", methods={"GET", "POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function editUserAction(Portal $portal, User $user, Request $request) {

        $originalPassword = $user->getPassword();

        $form = $this->createForm(EditUserType::class, $user);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        $formMarkup = $this->renderView(
            'Api/form/edit_user_form.html.twig',
            [
                'form' => $form->createView()
            ]
        );

        if($form->isSubmitted()) {

            $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
                $this->getUser(),
                Role::EDIT_USER,
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
            /** @var $user User */
            $formUser = $form->getData();
            $formUser->setPortal($portal);

            // only override the password if one is passed up
            if($formUser->getPassword()) {
                $formUser->setPassword($this->passwordEncoder->encodePassword(
                    $formUser,
                    $formUser->getPassword()
                ));
            } else {
                $formUser->setPassword($originalPassword);
            }

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
     * @Route("/{userId}/delete-form", name="delete_user_form", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param User $user
     * @return JsonResponse
     */
    public function getDeleteUserFormAction(Portal $portal, User $user) {

        $form = $this->createForm(DeleteUserType::class, $user);

        $formMarkup = $this->renderView(
            'Api/form/delete_user_form.html.twig',
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
     * @Route("/{userId}/delete", name="delete_user", methods={"POST"}, options={"expose" = true})
     * @param Portal $portal
     * @param User $user
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUserAction(Portal $portal, User $user, Request $request)
    {

        $hasPermission = $this->permissionAuthorizationHandler->isAuthorized(
            $this->getUser(),
            Role::DELETE_USER,
            Role::SYSTEM_PERMISSION
        );

        if(!$hasPermission) {
            return new JsonResponse(
                [
                    'success' => false,
                ], Response::HTTP_UNAUTHORIZED
            );
        }

        $form = $this->createForm(DeleteUserType::class, $user);

        $form->handleRequest($request);

        if (!$form->isValid()) {
            $formMarkup = $this->renderView(
                'Api/form/delete_user_form.html.twig',
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

        // delete user here
        /** @var $user User */
        $user = $form->getData();
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * @Route("/get-for-datatable", name="users_for_datatable", methods={"POST"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getUsersForDatatableAction(Portal $portal, Request $request) {

        $draw = intval($request->request->get('draw'));
        $start = $request->request->get('start');
        $length = $request->request->get('length');
        $search = $request->request->get('search');
        $orders = $request->request->get('order');
        $columns = $request->request->get('columns');
        $customFilters = $request->request->get('customFilters', []);

        $results = $this->userRepository->getDataTableData($portal, $start, $length, $search, $orders, $columns, $customFilters);

        $json = $this->serializer->serialize($results['results'], 'json', ['groups' => ['USERS_FOR_DATATABLE']]);

        $payload = json_decode($json, true);

        $totalReportCount = $this->userRepository->getTotalCount($portal);
        $filteredReportCount = count($payload);

        $response = new JsonResponse([
            'draw'  => $draw,
            'recordsFiltered' => !empty($search['value']) || !empty($customFilters) ? $filteredReportCount : $totalReportCount,
            'recordsTotal'  => $totalReportCount,
            'data'  => $payload
        ],  Response::HTTP_OK);

        return $response;

    }

    /**
     * @Route("/get-properties-for-filter/{internalName}", name="user_properties_for_filter", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param $internalName
     * @param Request $request
     * @return JsonResponse
     */
    public function getPropertiesForFilter(Portal $portal, $internalName, Request $request) {

        /**
         * Setup the filters that will be rendered for the user filter widget. This follows the same naming pattern
         * as Record Custom Filters and Reports. For example, a FieldCatalog::CUSTOM_OBJECT is just a join to another
         * table.
         */
        switch($internalName) {
            case 'root':

                $payload = [
                    [
                        'id' => 1,
                        'internalName' => 'email',
                        'label' => 'Email',
                        'fieldType' => FieldCatalog::SINGLE_LINE_TEXT,
                    ],
                    [
                        'id' => 2,
                        'internalName' => 'first_name',
                        'label' => 'First Name',
                        'fieldType' => FieldCatalog::SINGLE_LINE_TEXT,
                    ],
                    [
                        'id' => 3,
                        'internalName' => 'last_name',
                        'label' => 'Last Name',
                        'fieldType' => FieldCatalog::SINGLE_LINE_TEXT,
                    ],
                    [
                        'id' => 4,
                        'internalName' => 'is_active',
                        'label' => 'Is Active',
                        'fieldType' => FieldCatalog::SINGLE_CHECKBOX,
                    ],
                    [
                        'id' => 5,
                        'internalName' => 'is_admin_user',
                        'label' => 'Is Admin User',
                        'fieldType' => FieldCatalog::SINGLE_CHECKBOX,
                    ],
                    [
                        'id' => 6,
                        'internalName' => 'custom_roles',
                        'label' => 'Custom Roles',
                        'fieldType' => FieldCatalog::CUSTOM_OBJECT,
                    ]
                ];

                break;
            case 'custom_roles':

                $roles = $this->roleRepository->getRolesForUserFilterByPortal($portal);

                $payload = [
                    [
                        'id' => 7,
                        'internalName' => 'name',
                        'label' => 'Name',
                        'fieldType' => FieldCatalog::MULTIPLE_CHECKBOX,
                        'field' => [
                            'options' => $roles
                        ],
                    ]
                ];

                break;
        }

        return new JsonResponse([
            'success' => true,
            'data'  => $payload
        ], Response::HTTP_OK);
    }

}