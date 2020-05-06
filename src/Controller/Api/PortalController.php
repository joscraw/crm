<?php

namespace App\Controller\Api;

use App\Annotation\ApiRoute;
use App\Dto\CustomObject_Dto;
use App\Dto\Dto;
use App\Dto\DtoFactory;
use App\Dto\Portal_Dto;
use App\Entity\Portal;
use App\Entity\User;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Model\CustomObjectField;
use App\Model\FieldCatalog;
use App\Utils\ServiceHelper;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Entity\CustomObject;
use App\Http\Api;

use App\Model\Pagination\PaginationCollection;
use App\Dto\User_Dto;


/**
 * Class PortalController
 * @package App\Controller\Api
 */
class PortalController extends ApiController
{

    /**
     * Creates a New Portal
     *
     * Creates a portal in the platform.
     *
     * @ApiRoute("/portals/new", name="portal_new", methods={"POST"}, versions={"v1"}, scopes={"private", "marketing"})
     *
     * @SWG\Post(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"},
     *
     *    @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @Model(type=Portal_Dto::class, groups={Dto::GROUP_CREATE})
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
     *          response=201,
     *          description="Returns a newly created portal",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=Portal_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     *         description="Error: Bad Request.",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="message", type="string", example="There was a validation error"),
     *              @SWG\Property(property="code", type="string", example="validation_error"),
     *              @SWG\Property(property="errors", type="object",
     *                    @SWG\Property(property="name", type="array",
     *                          @SWG\Items(type="string", example="Please don't forget a name.")
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
     * @SWG\Tag(name="Portal")
     *
     * @param Request $request
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     * @throws \App\Exception\DataTransformerNotFoundException
     */
    public function new(Request $request) {

        // todo we need this endpoint private but need to still allow access to the marketing
        // todo site without making travis send up a username and password for an access token right?

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        $dto = $this->dtoFactory->create(DtoFactory::PORTAL, $version);

        /** @var Portal_Dto $dto */
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
        /** @var Portal $portal */
        $portal = $dataTransformer->reverseTransform($dto);
        $role = $this->permissionManager->configureSuperAdminRole($portal);
        $this->entityManager->persist($portal);
        $this->entityManager->persist($role);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($portal),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_CREATED, [
            'Location' => !empty(json_decode($json, true)['_links']['view']) ? json_decode($json, true)['_links']['view'] : ''
        ], true);

    }

    /**
     * Fetch a Portal
     *
     * Fetches a portal in the platform.
     *
     * @ApiRoute("/portals/{id}/view", name="portal_view", methods={"GET"}, versions={"v1"}, scopes={"private"})
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
     *          description="Returns the desired portal.",
     *          @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="data", ref=@Model(type=Portal_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *    ),
     *
     *    @SWG\Response(
     *      response=400,
     *      description="Error: Bad Request.",
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
     * @SWG\Tag(name="Portal")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Portal $portal
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function view(Request $request, Portal $portal) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        $dto = $this->dtoFactory->create(DtoFactory::PORTAL, $version, true);

        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());

        $json = $this->serializer->serialize(
            $dataTransformer->transform($portal),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * Edit a Portal
     *
     * Edit a portal in the platform.
     *
     * @ApiRoute("/portals/{id}/edit", name="portal_edit", methods={"PATCH"}, versions={"v1"}, scopes={"private"})
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
     *         @Model(type=Portal_Dto::class, groups={Dto::GROUP_UPDATE})
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
     *          description="Returns the updated portal",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=Portal_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *     ),
     *
     *     @SWG\Response(
     *         response=400,
     *         description="Error: Bad Request.",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="message", type="string", example="There was a validation error"),
     *              @SWG\Property(property="code", type="string", example="validation_error"),
     *              @SWG\Property(property="errors", type="object",
     *                    @SWG\Property(property="name", type="array",
     *                          @SWG\Items(type="string", example="Please don't forget a name for your portal.")
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
     * @SWG\Tag(name="Portal")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Portal $portal
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function edit(Request $request, Portal $portal) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        // First transform the custom object to our DTO
        /** @var Dto $dto */
        $dto = $this->dtoFactory->create(DtoFactory::PORTAL, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        /** @var Dto $dto */
        $dto = $dataTransformer->transform($portal);

        // populate the DTO with the request content
        /** @var Dto $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $this->dtoFactory->create(DtoFactory::PORTAL, $version),
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
        /** @var Portal $portal */
        $portal = $dataTransformer->reverseTransform($dto);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($portal),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * Delete a Portal
     *
     * Delete a portal in the platform.
     *
     * @ApiRoute("/portals/{id}/delete", name="portal_delete", methods={"DELETE"}, versions={"v1"}, scopes={"private"})
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
     *          description="Returns the deleted portal",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=Portal_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *     ),
     *
     *    @SWG\Response(
     *      response=400,
     *      description="Error: Bad Request.",
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
     * @SWG\Tag(name="Portal")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param Portal $portal
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function delete(Request $request, Portal $portal) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        // First transform the custom object to our DTO
        /** @var Dto $dto */
        $dto = $this->dtoFactory->create(DtoFactory::PORTAL, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        /** @var Dto $dto */
        $dto = $dataTransformer->transform($portal);

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

        $this->entityManager->remove($portal);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dto,
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

}