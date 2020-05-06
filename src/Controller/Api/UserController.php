<?php

namespace App\Controller\Api;

use App\Entity\Portal;
use App\Entity\Role;
use App\Entity\User;
use App\Form\DeleteUserType;
use App\Form\EditUserType;
use App\Form\UserType;
use App\Model\FieldCatalog;
use App\Model\Pagination\PaginationCollection;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Annotation\ApiRoute;
use App\Dto\CustomObject_Dto;
use App\Dto\Dto;
use App\Dto\DtoFactory;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use Symfony\Component\Form\DataTransformerInterface;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Entity\CustomObject;
use App\Http\Api;
use App\Dto\User_Dto;
use App\Dto\Role_Dto;

/**
 * Class UserController
 * @package App\Controller\Api
 *
 */
class UserController extends ApiController
{

    /**
     * Creates a User
     *
     * Creates a user in the platform and in auth0.
     *
     * @ApiRoute("/users/new", name="user_new", methods={"POST"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Post(
     *     description=Api::DESCRIPTION,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @Model(type=User_Dto::class, groups={Dto::GROUP_CREATE})
     *     ),
     *
     *    @SWG\Parameter(
     *     name="XDEBUG_SESSION_START",
     *     in="query",
     *     type="string",
     *     description="Triggers an Xdebug Session",
     *     default="PHPSTORM"
     *    ),
     *
     *    @SWG\Parameter(
     *     name="verbosity",
     *     in="query",
     *     type="string",
     *     description="Set any value here for a more descriptive error message in the response. Should only be used for debugging purposes only and never in production!"
     *    ),
     *
     *     @SWG\Response(
     *          response=201,
     *          description="Returns a newly created user",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=User_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          ),
     *          @SWG\Header(
     *              header="Location",
     *              description="The location to the newly created resource",
     *              type="string"
     *          )
     *     ),
     *
     *     @SWG\Response(
     *         response=400,
     *         description="Validation errors.",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="message", type="string", example="There was a validation error"),
     *              @SWG\Property(property="code", type="string", example="validation_error"),
     *              @SWG\Property(property="errors", type="object",
     *                    @SWG\Property(property="label", type="array",
     *                          @SWG\Items(type="string", example="Please don't forget a label for your custom object.")
     *                     ),
     *                     @SWG\Property(property="internal_name", type="array",
     *                          @SWG\Items(type="string", example="Please don't forget to add an internal name for your custom object.")
     *                     )
     *              )
     *         )
     *     ),
     *
     *     @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="JWT expired. Please request a refresh.")
     *          )
     *     ),
     *     @SWG\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Internal server error. Infinite recursion detected.")
     *          )
     *    )
     *
     * )
     *
     *
     * @SWG\Tag(name="Users")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function new(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        /** @var Portal $portal */
        $portal = $this->portalResolver->resolve();

        $dto = $this->dtoFactory->create(DtoFactory::CUSTOM_OBJECT, $version);

        /** @var CustomObject_Dto $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $dto,
            'json',
            ['groups' => Dto::GROUP_CREATE]
        );

        $validationErrors = $this->validator->validate($dto, null, [Dto::GROUP_CREATE]);

        if (count($validationErrors) > 0) {
            return new ApiErrorResponse(
                null,
                ApiErrorResponse::TYPE_VALIDATION_ERROR,
                $this->getErrorsFromValidator($validationErrors),
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        /** @var CustomObject $customObject */
        $customObject = $dataTransformer->reverseTransform($dto);
        $customObject->setPortal($portal);
        $this->entityManager->persist($customObject);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($customObject),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_CREATED, [
            'Location' => !empty(json_decode($json, true)['_links']['view']) ? json_decode($json, true)['_links']['view'] : ''
        ], true);
    }

    /**
     * Assign roles to a user
     *
     * Assign roles to a user in the platform.
     *
     * @ApiRoute("/users/{id}/roles/add", name="user_roles_add", methods={"POST"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Post(
     *     description=Api::USER_CONTROLLER_USER_ROLES_ADD,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         format="application/json",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="roles", type="array", example={19,20},
     *                    @SWG\Items(type="integer")
     *              )
     *         )
     *     ),
     *
     *    @SWG\Parameter(
     *     name="XDEBUG_SESSION_START",
     *     in="query",
     *     type="string",
     *     description="Triggers an Xdebug Session",
     *     default="PHPSTORM"
     *    ),
     *
     *    @SWG\Parameter(
     *     name="verbosity",
     *     in="query",
     *     type="string",
     *     description="Set any value here for a more descriptive error message in the response. Should only be used for debugging purposes only and never in production!"
     *    ),
     *
     *    @SWG\Response(
     *          response=200,
     *          description="Returns the newly assigned roles.",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", type="array", @Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *     ),
     *
     *     @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="JWT expired. Please request a refresh.")
     *          )
     *     ),
     *
     *      @SWG\Response(
     *         response=404,
     *         description="Not found",
     *         @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Not found.")
     *         )
     *     ),
     *
     *     @SWG\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Internal server error. Infinite recursion detected.")
     *          )
     *    )
     *
     * )
     *
     *
     * @SWG\Tag(name="Users")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param User $user
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function addUserRoles(Request $request, User $user) {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $roleIds = $request->request->get('roles', []);

        $roles = $this->roleRepository->findBy([
            'id' => $roleIds
        ]);

        foreach($roles as $role) {
            $user->addCustomRole($role);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // we need to proxy all these custom objects through our transformer.
        $dto = $this->dtoFactory->create(DtoFactory::ROLE, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        $dtos = [];
        foreach ($roles as $role) {
            /** @var Dto $dto */
            $dtos[] = $dataTransformer->transform($role);
        }

        $json = $this->serializer->serialize($dtos, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);
    }

    /**
     * Remove roles from a user
     *
     * Remove roles from a user in the platform.
     *
     * @ApiRoute("/users/{id}/roles/remove", name="user_roles_remove", methods={"DELETE"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Delete(
     *     description=Api::USER_CONTROLLER_USER_ROLES_REMOVE,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="roles", type="array", example={19,20},
     *                    @SWG\Items(type="integer")
     *              )
     *         )
     *     ),
     *
     *    @SWG\Parameter(
     *     name="XDEBUG_SESSION_START",
     *     in="query",
     *     type="string",
     *     description="Triggers an Xdebug Session",
     *     default="PHPSTORM"
     *    ),
     *
     *    @SWG\Parameter(
     *     name="verbosity",
     *     in="query",
     *     type="string",
     *     description="Set any value here for a more descriptive error message in the response. Should only be used for debugging purposes only and never in production!"
     *    ),
     *
     *    @SWG\Response(
     *          response=200,
     *          description="Returns the removed roles.",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", type="array", @Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *     ),
     *
     *     @SWG\Response(
     *          response=400,
     *          description="Error: Bad Request",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Invalid Request format.")
     *          )
     *     ),
     *
     *     @SWG\Response(
     *          response=401,
     *          description="Unauthorized",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="JWT expired. Please request a refresh.")
     *          )
     *     ),
     *
     *     @SWG\Response(
     *         response=404,
     *         description="Not found",
     *         @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Not found.")
     *         )
     *     ),
     *
     *     @SWG\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Internal server error. Infinite recursion detected.")
     *          )
     *    )
     *
     * )
     *
     *
     * @SWG\Tag(name="Users")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param User $user
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function removeUserRoles(Request $request, User $user) {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $roleIds = $request->request->get('roles', []);

        $roles = $this->roleRepository->findBy([
            'id' => $roleIds
        ]);

        foreach($roles as $role) {
            $user->removeCustomRole($role);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // we need to proxy all these custom objects through our transformer.
        $dto = $this->dtoFactory->create(DtoFactory::ROLE, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        $dtos = [];
        foreach ($roles as $role) {
            /** @var Dto $dto */
            $dtos[] = $dataTransformer->transform($role);
        }

        $json = $this->serializer->serialize($dtos, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);

    }

    /**
     * Get Roles for a user
     *
     * Lists the roles for a user in the platform
     *
     * @ApiRoute("/users/{id}/roles/view", name="user_roles_view", methods={"GET"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Get(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"}
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the roles for a user in the platform",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="total", type="integer", example=135, description="Total count of all items."),
     *          @SWG\Property(property="count", type="integer", example=12, description="Count of items returned in response."),
     *          @SWG\Property(property="data", type="array", @Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT})),
     *          @SWG\Property(property="_links", type="object",
     *              @SWG\Property(property="self", type="string", example="/api/v1/private/users/1/roles/view?page=3"),
     *              @SWG\Property(property="first", type="string", example="/api/v1/private/users/1/roles/view?page=1"),
     *              @SWG\Property(property="last", type="string", example="/api/v1/private/users/1/roles/view?page=8"),
     *              @SWG\Property(property="next", type="string", example="/api/v1/private/users/1/roles/view?page=4"),
     *              @SWG\Property(property="prev", type="string", example="/api/v1/private/users/1/roles/view?page=2")
     *          )
     *     )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Bad Request",
     *     @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Invalid Request format.")
     *      )
     * )
     *
     * @SWG\Response(
     *     response=401,
     *     description="Unauthorized",
     *     @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="JWT expired. Please request a refresh.")
     *      )
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="Internal Server Error",
     *     @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Internal server error. Infinite recursion detected.")
     *      )
     * )
     *
     *
     * @SWG\Parameter(
     *     name="page",
     *     in="query",
     *     type="integer",
     *     description="The page you want to return"
     * )
     *
     * @SWG\Parameter(
     *     name="limit",
     *     in="query",
     *     type="integer",
     *     description="The number of results to return per page (leave empty to default to all)"
     * )
     *
     * @SWG\Parameter(
     *     name="XDEBUG_SESSION_START",
     *     in="query",
     *     type="string",
     *     description="Triggers an Xdebug Session",
     *     default="PHPSTORM"
     * )
     *
     * @SWG\Parameter(
     *     name="verbosity",
     *     in="query",
     *     type="string",
     *     description="Set any value here for a more descriptive error message in the response. Should only be used for debugging purposes only and never in production!"
     * )
     *
     *
     * @SWG\Tag(name="Users")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param User $user
     * @return ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function getUserRoles(Request $request, User $user)
    {
        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', null);

        $qb = $this->roleRepository->findAllQueryBuilder()
            ->innerJoin('role.users', 'users')
            ->andWhere('users.id = :id')
            ->setParameter('id', $user->getId());

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setAllowOutOfRangePages(true);

        if($limit) {
            $pagerfanta->setMaxPerPage($limit);
        }

        $pagerfanta->setCurrentPage($page);

        $roles = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $roles[] = $result;
        }

        // we need to proxy all these custom objects through our transformer.
        $dto = $this->dtoFactory->create(DtoFactory::ROLE, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        $dtos = [];
        foreach ($roles as $role) {
            /** @var Dto $dto */
            $dtos[] = $dataTransformer->transform($role);
        }

        $paginationCollection = new PaginationCollection($dtos, $pagerfanta);

        $json = $this->serializer->serialize($paginationCollection, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/{internalIdentifier}/api/users/create", name="create_user", methods={"GET", "POST"}, options = { "expose" = true })
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
     * @Route("/{internalIdentifier}/api/users/{userId}/edit", name="edit_user", methods={"GET", "POST"}, options = { "expose" = true })
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
     * @Route("/{internalIdentifier}/api/users/{userId}/delete-form", name="delete_user_form", methods={"GET"}, options = { "expose" = true })
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
     * @Route("/{internalIdentifier}/api/users/{userId}/delete", name="delete_user", methods={"POST"}, options={"expose" = true})
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
     * @Route("/{internalIdentifier}/api/users/get-for-datatable", name="users_for_datatable", methods={"POST"}, options = { "expose" = true })
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
     * @Route("/{internalIdentifier}/api/users/get-properties-for-filter/{internalName}", name="user_properties_for_filter", methods={"GET"}, options = { "expose" = true })
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