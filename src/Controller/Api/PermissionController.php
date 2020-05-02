<?php

namespace App\Controller\Api;

use App\Annotation\ApiRoute;
use App\Dto\DtoFactory;
use App\Entity\Portal;
use App\Entity\Role;
use App\Entity\User;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Model\Pagination\PaginationCollection;
use App\Security\Auth\PermissionManager;
use App\Utils\ServiceHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Nelmio\ApiDocBundle\Annotation\Model;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Entity\CustomObject;
use App\Http\Api;
use App\Dto\Dto;
use App\Dto\Role_Dto;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;


/**
 * Class PermissionController
 * @package App\Controller\Api
 */
class PermissionController extends ApiController
{

    /**
     * Get permission templates
     *
     * Lists all of the available permission templates in the platform
     *
     *
     * @ApiRoute("/permission-templates", name="permission_templates", methods={"GET"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Get(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"},
     *
     *    @SWG\Response(
     *          response=200,
     *          description=Api::PERMISSION_DESCRIPTION,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", type="object",
     *                      @SWG\Property(property="portal", type="array", example={"portal_*", "portal_:portalid"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="custom_object", type="array", example={"customobject_:customobjectid","portal_:portalid_customobject_*"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="property_group", type="array", example={"propertygroup_*", "customobject_:customobjectid_propertygroup_*"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="property", type="array", example={"property_*", "property_:propertyid"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="record", type="array", example={"portal_:portalid_record_*", "customobject_:customobjectid_record_*"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="class_specific", type="array", example={"portal_*", "customobject_*"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="object_specific", type="array", example={"portal_:portalid", "customobject_:customobjectid"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="hybrid", type="array", example={"portal_:portalid_customobject_*", "portal_:portalid_propertygroup_*"}, @SWG\Items(type="string")),
     *                      @SWG\Property(property="attribute_specific", type="array", example={"can_login", "can_configure_roles_and_permissions"}, @SWG\Items(type="string"))
     *              )
     *          )
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
     * @throws \ReflectionException
     */
    public function permissionTemplates(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        return new ApiResponse(null, PermissionManager::$templates, Response::HTTP_OK, []);
    }

    /**
     * Edit permissions for a user
     *
     * Edit permissions for a user in platform.
     *
     * @ApiRoute("/permissions/users/{id}/edit", name="edit_user_permissions", methods={"PATCH"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Patch(
     *     description=Api::DESCRIPTION,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload where each property name is a permission template and each value is an array of permission bits you want to assign to that template.",
     *         format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="portal_*", type="array", example={1,2},
     *                  @SWG\Items(type="integer", description="the permission bits you want to assign for a given key")),
     *              @SWG\Property(property="portal_1_customobject_*", type="array", example={4,8},
     *                  @SWG\Items(type="integer", description="the permission bits you want to assign for a given key"))
     *          )
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
     *          response=204,
     *          description="No Content On Success."
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
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param User $user
     * @return ApiErrorResponse|ApiResponse
     */
    public function editUserPermissions(Request $request, User $user) {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $permissions = [];
        foreach($request->request->all() as $key => $bits) {
            $permissions[$key] = array_sum($bits);
        }

        // todo just food for thought on roles/permissions. We need to combine role/user
        //  permissions. I'm thinking  either whichever has the highest privilege then
        //  that one should take presidency.
        $user->setPermissions($permissions);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new ApiResponse(null, null,Response::HTTP_NO_CONTENT, []);
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
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        /** @var Portal $portal */
        $portal = $this->portalResolver->resolve();

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', null);

        $qb = $this->roleRepository->findAllQueryBuilder($portal);

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
     * Create a Role
     *
     * Creates a user defined role in the platform.
     *
     * @ApiRoute("/roles/new", name="role_new", methods={"POST"}, versions={"v1"}, scopes={"private"})
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
     *         @Model(type=Role_Dto::class, groups={Dto::GROUP_CREATE})
     *     ),
     *
     *     @SWG\Parameter(
     *     name="internalIdentifier",
     *     in="query",
     *     type="string",
     *     description="The portal internal identifier. If not specified will always default to the portal attached to the user object. This is almost always the portal the user was initially created under."
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

        if(false === $this->permissionManager->isAuthorized('can_configure_roles_and_permissions',PermissionManager::MASK_ENABLED, $user->getPermissions())) {
            throw new AccessDeniedHttpException("You do not have valid permissions to be modifying user roles.");
        }

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
     * Edits a user defined role in the platform.
     *
     * @ApiRoute("/roles/{id}/edit", name="role_edit", methods={"PATCH"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Patch(
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

        /** @var Portal $portal */
        $portal = $this->portalResolver->resolve();

        if(false === $this->permissionManager->isAuthorized('can_configure_roles_and_permissions',PermissionManager::MASK_ENABLED, $user->getPermissions())) {
            throw new AccessDeniedHttpException("You do not have valid permissions to be modifying user roles.");
        }

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
     * @SWG\Tag(name="Permissions")
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

        /** @var Portal $portal */
        $portal = $this->portalResolver->resolve();

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', null);

        $qb = $this->roleRepository->findAllQueryBuilder($portal)
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
     * Edit roles for a user
     *
     * Edit roles for a user in platform.
     *
     * @ApiRoute("/users/{id}/roles/edit", name="edit_user_roles", methods={"PATCH"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Patch(
     *     description=Api::DESCRIPTION,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload that accepts an array of role objects to assign to the user",
     *         format="application/json",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="roles", type="array", example={1,2}, @SWG\Items(type="integer"))
     *          )
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
     *          response=204,
     *          description="No Content On Success."
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
     * @SWG\Tag(name="Permissions")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param User $user
     * @return ApiErrorResponse|ApiResponse
     */
    public function editUserRoles(Request $request, User $user) {

        /** @var User $loggedInUser */
        $loggedInUser = $this->getUser();
        $version = $request->headers->get('X-Accept-Version');

        $data = json_decode($request->getContent(), true);
        $roles = $data['roles'];

        $roles = $this->roleRepository->findBy([
            'id' => $roles
        ]);

        $originalRoles = new ArrayCollection();
        foreach ($user->getCustomRoles() as $role) {
            $originalRoles->add($role);
        }

        /** @var Role $originalRole */
        foreach ($originalRoles as $originalRole) {
            if (false === (new ArrayCollection($roles))->contains($originalRole)) {
                $originalRole->removeUser($user);
                $this->entityManager->persist($originalRole);
            }
        }

        foreach($roles as $role) {
            if(!$user->getCustomRoles()->contains($role)) {
                $user->addCustomRole($role);
            }
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new ApiResponse(null, null,Response::HTTP_NO_CONTENT, []);
    }

    /**
     * Delete a role
     *
     * Delete a user defined role in the platform.
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
     *          @SWG\Property(property="data", ref=@Model(type=Role_Dto::class, groups={Dto::GROUP_DEFAULT}))
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

}