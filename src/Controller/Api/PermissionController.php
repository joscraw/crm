<?php

namespace App\Controller\Api;

use App\Annotation\ApiRoute;
use App\Entity\User;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Security\Auth\PermissionManager;
use App\Utils\ServiceHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Entity\CustomObject;
use App\Http\Api;


/**
 * Class PermissionController
 * @package App\Controller\Api
 */
class PermissionController extends ApiController
{
    use ServiceHelper;

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
     *          description="Returns all the avialable permissions in the platform."
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
     * @throws \App\Exception\PermissionKeyNotFoundException
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
     *         description="JSON payload",
     *         format="application/json",
     *          @SWG\Schema(
     *              type="array",
     *              @SWG\Items(type="object", description="the objects",
     *                  @SWG\Property(property="key", type="string", example="custom_object"),
     *                  @SWG\Property(property="bits", type="array", example={1,2},
     *                          @SWG\Items(type="integer", description="the permission bits you want to assign for a given key")
     *                  )
     *              )
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
     *          response=200,
     *          description="Returns the updated permissions",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", type="object",
     *                    @SWG\Property(property="label", type="array",
     *                          @SWG\Items(type="string", example="Please don't forget a label for your custom object.")
     *                     )
     *              )
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
     * @param CustomObject $customObject
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function editPermissions(Request $request, CustomObject $customObject) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        $permissions = [];
        foreach($request->request->all() as $permission) {
            $permissions[$permission['key']] = array_sum($permission['bits']);
        }
        $user->setPermissions($permissions);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new ApiResponse(null, $permissions,Response::HTTP_OK, []);
    }
}