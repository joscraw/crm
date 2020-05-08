<?php

namespace App\Controller\Api;

use App\Annotation\ApiRoute;
use App\Dto\DtoFactory;
use App\Entity\AclEntry;
use App\Entity\Portal;
use App\Entity\Role;
use App\Entity\User;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Model\Pagination\PaginationCollection;
use Nelmio\ApiDocBundle\Annotation\Model;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Http\Api;
use App\Dto\Dto;
use App\Dto\Role_Dto;
use App\Dto\Permission_Dto;
use App\Dto\AclEntry_Dto;
use App\Dto\AclLock_Dto;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class PermissionController
 * @package App\Controller\Api
 */
class PermissionController extends ApiController
{

    /**
     * @Route("/haha/test", name="ha_ha_test", methods={"GET"}, options = { "expose" = true })
     * @param Request $request
     * @return JsonResponse
     */
    public function getRecordFormAction(Request $request) {

        return new JsonResponse(
            [
                'success' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Get Roles
     *
     * Lists the roles in the platform
     *
     * @ApiRoute("/roles", name="roles", methods={"GET"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Get(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"}
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the roles in the platform",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="total", type="integer", example=135, description="Total count of all items."),
     *          @SWG\Property(property="count", type="integer", example=12, description="Count of items returned in response."),
     *          @SWG\Property(property="data", type="array", @Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT})),
     *          @SWG\Property(property="_links", type="object",
     *              @SWG\Property(property="self", type="string", example="/api/v1/private/roles?page=3"),
     *              @SWG\Property(property="first", type="string", example="/api/v1/private/roles?page=1"),
     *              @SWG\Property(property="last", type="string", example="/api/v1/private/roles?page=8"),
     *              @SWG\Property(property="next", type="string", example="/api/v1/private/roles?page=4"),
     *              @SWG\Property(property="prev", type="string", example="/api/v1/private/roles?page=2")
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
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return ApiResponse
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     * @throws \App\Exception\DataTransformerNotFoundException
     */
    public function getRoles(Request $request) {

        // todo add query parameter here to limit scope of roles returned by a given portal
        // todo add query parameter to limit scope of roles returned by a given user
        // todo add query parameters to add permissions to response


        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', null);

        $qb = $this->roleRepository->findAllQueryBuilder();

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
     * Get Permissions
     *
     * Lists the permissions in the platform
     *
     * @ApiRoute("/permissions", name="roles", methods={"GET"}, versions={"v1"}, scopes={"public"})
     *
     * @SWG\Get(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"}
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the permissions in the platform",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="data", type="array", @Model(type=Permission_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     * @SWG\Tag(name="Permissions")
     *
     * @param Request $request
     * @return ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function permissions(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        $permissions = $this->permissionRepository->findAll();

        // we need to proxy all these custom objects through our transformer.
        $dto = $this->dtoFactory->create(DtoFactory::PERMISSION, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        $dtos = [];
        foreach ($permissions as $permission) {
            /** @var Dto $dto */
            $dtos[] = $dataTransformer->transform($permission);
        }

        $json = $this->serializer->serialize($dtos, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Role
     *
     * Creates a role in the platform.
     *
     * @ApiRoute("/roles/new", name="role_new", methods={"POST"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Post(
     *     description=Api::PERMISSION_CONTROLLER_ROLE_NEW,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @Model(type=Role_Dto::class, groups={Dto::GROUP_CREATE})
     *     ),
     *
     *     @SWG\Parameter(
     *     name="internalIdentifier",
     *     in="query",
     *     type="string",
     *     description="The portal internal identifier. If not specified the role will be created at the global level and apply to all portals in the application."
     *    ),
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
     *          description="Returns a newly created role",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     *                          @SWG\Items(type="string", example="Please don't forget a name for your role.")
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
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function newRole(Request $request) {

        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        /** @var Portal $portal */
        $portal = $this->portalResolver->resolve();

        $dto = $this->dtoFactory->create(DtoFactory::ROLE, $version);

        /** @var Role_Dto $dto */
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
        /** @var Role $role */
        $role = $dataTransformer->reverseTransform($dto);
        $role->setPortal($portal);
        $this->entityManager->persist($role);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($role),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_CREATED, [
            'Location' => !empty(json_decode($json, true)['_links']['view']) ? json_decode($json, true)['_links']['view'] : '',
        ], true);
    }

    /**
     * Edit a Role
     *
     * Edits a role in the platform.
     *
     * @ApiRoute("/roles/{id}/edit", name="role_edit", methods={"PATCH"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Patch(
     *     description=Api::PERMISSION_CONTROLLER_ROLE_EDIT,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @Model(type=Role_Dto::class, groups={Dto::GROUP_UPDATE})
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
     *          response=200,
     *          description="Returns the updated role",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     *                          @SWG\Items(type="string", example="Please don't forget a namee for your role.")
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
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Role $role
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function editRole(Request $request, Role $role) {

        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $this->denyAccessUnlessGranted('update', $role);

        // First transform the role to our DTO
        /** @var Dto $dto */
        $dto = $this->dtoFactory->create(DtoFactory::ROLE, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        /** @var Dto $dto */
        $dto = $dataTransformer->transform($role);

        // populate the DTO with the request content
        /** @var Dto $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $this->dtoFactory->create(DtoFactory::ROLE, $version),
            'json',
            ['groups' => Dto::GROUP_UPDATE, 'object_to_populate' => $dto]
        );

        // validate the object
        $validationErrors = $this->validator->validate($dto, null, [Dto::GROUP_UPDATE]);
        if (count($validationErrors) > 0) {
            return new ApiErrorResponse(
                null,
                ApiErrorResponse::TYPE_VALIDATION_ERROR,
                $this->getErrorsFromValidator($validationErrors),
                Response::HTTP_BAD_REQUEST
            );
        }

        // convert the DTO back into a custom object and save to the db
        /** @var Role $role */
        $role = $dataTransformer->reverseTransform($dto);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($role),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * Add Role Permissions
     *
     * Add permissions to a role.
     *
     * @ApiRoute("/roles/{id}/permissions/add", name="role_permissions_add", methods={"POST"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Post(
     *     description=Api::PERMISSION_CONTROLLER_ROLE_PERMISSIONS_ADD,
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
     *              @SWG\Property(property="permissions", type="array", example={19,20},
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
     *          description="Returns the newly added permissions.",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", type="array", @Model(type=Permission_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Role $role
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function addRolePermissions(Request $request, Role $role) {

        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $permissionIds = $request->request->get('permissions', []);

        $permissions = $this->permissionRepository->findBy([
            'id' => $permissionIds
        ]);

        foreach($permissions as $permission) {
            $role->addPermission($permission);
        }

        $this->entityManager->persist($role);
        $this->entityManager->flush();

        // we need to proxy all these custom objects through our transformer.
        $dto = $this->dtoFactory->create(DtoFactory::PERMISSION, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        $dtos = [];
        foreach ($permissions as $permission) {
            /** @var Dto $dto */
            $dtos[] = $dataTransformer->transform($permission);
        }

        $json = $this->serializer->serialize($dtos, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);

    }

    /**
     * Remove Role Permissions
     *
     * Remove permissions from a role.
     *
     * @ApiRoute("/roles/{id}/permissions/remove", name="role_permissions_remove", methods={"DELETE"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Delete(
     *     description=Api::PERMISSION_CONTROLLER_ROLE_PERMISSIONS_REMOVE,
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
     *              @SWG\Property(property="permissions", type="array", example={19,20},
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
     *          description="Returns the removed permissions.",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", type="array", @Model(type=Permission_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Role $role
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function removeRolePermissions(Request $request, Role $role) {

        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $permissionIds = $request->request->get('permissions', []);

        $permissions = $this->permissionRepository->findBy([
            'id' => $permissionIds
        ]);

        foreach($permissions as $permission) {
            $role->removePermission($permission);
        }

        $this->entityManager->flush();

        // we need to proxy all these custom objects through our transformer.
        $dto = $this->dtoFactory->create(DtoFactory::PERMISSION, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        $dtos = [];
        foreach ($permissions as $permission) {
            /** @var Dto $dto */
            $dtos[] = $dataTransformer->transform($permission);
        }

        $json = $this->serializer->serialize($dtos, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);

    }

    /**
     * Delete a role
     *
     * Delete a role in the platform.
     *
     * @ApiRoute("/roles/{id}/delete", name="role_delete", methods={"DELETE"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Delete(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"},
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
     *          response=200,
     *          description="Returns the deleted role",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *     ),
     *
     *    @SWG\Response(
     *      response=400,
     *      description="Bad Request.",
     *      @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Invalid Request format.")
     *      )
     *    ),
     *
     *    @SWG\Response(
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
     *      )
     *
     * )
     *
     *
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Role $role
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function deleteRole(Request $request, Role $role) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        // First transform the role to our DTO
        /** @var Dto $dto */
        $dto = $this->dtoFactory->create(DtoFactory::ROLE, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        /** @var Dto $dto */
        $dto = $dataTransformer->transform($role);

        // validate the object
        $validationErrors = $this->validator->validate($dto, null, [Dto::GROUP_DELETE]);
        if (count($validationErrors) > 0) {
            return new ApiErrorResponse(
                null,
                ApiErrorResponse::TYPE_VALIDATION_ERROR,
                $this->getErrorsFromValidator($validationErrors),
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->entityManager->remove($role);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dto,
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * Fetch a Role.
     *
     * Fetches a role in the platform along with it's permissions.
     *
     * @ApiRoute("/roles/{id}/view", name="role_view", methods={"GET"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Get(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"},
     *
     *    @SWG\Parameter(
     *     name="XDEBUG_SESSION_START",
     *     in="query",
     *     type="string",
     *     description="Triggers an Xdebug Session.",
     *     default="PHPSTORM"
     *    ),
     *
     *   @SWG\Parameter(
     *     name="verbosity",
     *     in="query",
     *     type="string",
     *     description="Set any value here for a more descriptive error message in the response. Should only be used for debugging purposes only and never in production!"
     *    ),
     *
     *    @SWG\Response(
     *          response=200,
     *          description="Returns the desired role along with it's permissions.",
     *          @SWG\Schema(
     *          type="object",
     *              @SWG\Property(property="data", ref=@Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *    ),
     *
     *    @SWG\Response(
     *      response=400,
     *      description="Bad Request.",
     *      @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Invalid Request format.")
     *      )
     *    ),
     *
     *    @SWG\Response(
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
     *      )
     *
     *
     * )
     *
     *
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Role $role
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function viewRole(Request $request, Role $role) {

        // todo add query parameter here to fetch it's connected permissions if the end user wants


        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        $dto = $this->dtoFactory->create(DtoFactory::ROLE, $version, true);

        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());

        $json = $this->serializer->serialize(
            $dataTransformer->transform($role),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * ACL add entry to a given object, class, and/or field
     *
     * @ApiRoute("/acl/entry/add", name="acl_entry_add", methods={"POST"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Post(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @Model(type=AclEntry_Dto::class, groups={Dto::GROUP_CREATE})
     *     ),
     *
     *    @SWG\Parameter(
     *     name="XDEBUG_SESSION_START",
     *     in="query",
     *     type="string",
     *     description="Triggers an Xdebug Session.",
     *     default="PHPSTORM"
     *    ),
     *
     *   @SWG\Parameter(
     *     name="verbosity",
     *     in="query",
     *     type="string",
     *     description="Set any value here for a more descriptive error message in the response. Should only be used for debugging purposes only and never in production!"
     *    ),
     *
     *    @SWG\Response(
     *          response=201,
     *          description="Returns the newly created AclEntry.",
     *          @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="data", ref=@Model(type=AclEntry_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *    ),
     *
     *    @SWG\Response(
     *      response=400,
     *      description="Bad Request.",
     *      @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Invalid Request format.")
     *      )
     *    ),
     *
     *    @SWG\Response(
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
     *      )
     *
     *
     * )
     *
     *
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function aclAddEntry(Request $request) {

        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $dto = $this->dtoFactory->create(DtoFactory::ACL_ENTRY, $version);

        /** @var Role_Dto $dto */
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
        /** @var AclEntry $aclEntry */
        $aclEntry = $dataTransformer->reverseTransform($dto);

        $grants = $this->permissionManager->resolveGrants([$aclEntry]);
        if(!empty($grants[0])) {
            $aclEntry->setGrantingStrategy($grants[0]);
        }

        $this->entityManager->persist($aclEntry);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($aclEntry),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_CREATED, [
            'Location' => !empty(json_decode($json, true)['_links']['view']) ? json_decode($json, true)['_links']['view'] : '',
        ], true);

    }

    /**
     * ACL add lock for a given object, class, and/or field
     *
     * @ApiRoute("/acl/lock/add", name="acl_lock_add", methods={"POST"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Post(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @Model(type=AclLock_Dto::class, groups={Dto::GROUP_CREATE})
     *     ),
     *
     *    @SWG\Parameter(
     *     name="XDEBUG_SESSION_START",
     *     in="query",
     *     type="string",
     *     description="Triggers an Xdebug Session.",
     *     default="PHPSTORM"
     *    ),
     *
     *   @SWG\Parameter(
     *     name="verbosity",
     *     in="query",
     *     type="string",
     *     description="Set any value here for a more descriptive error message in the response. Should only be used for debugging purposes only and never in production!"
     *    ),
     *
     *    @SWG\Response(
     *          response=200,
     *          description="Returns the newly created AclLock.",
     *          @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="data", ref=@Model(type=AclLock_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *    ),
     *
     *    @SWG\Response(
     *      response=400,
     *      description="Bad Request.",
     *      @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Invalid Request format.")
     *      )
     *    ),
     *
     *    @SWG\Response(
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
     *      )
     *
     * )
     *
     *
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function aclAddLock(Request $request) {

        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $dto = $this->dtoFactory->create(DtoFactory::ACL_LOCK, $version);

        /** @var AclLock_Dto $dto */
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
        /** @var AclEntry $aclEntry */
        $aclLock = $dataTransformer->reverseTransform($dto);
        $this->entityManager->persist($aclLock);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($aclLock),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_CREATED, [
            'Location' => !empty(json_decode($json, true)['_links']['view']) ? json_decode($json, true)['_links']['view'] : '',
        ], true);

    }
}