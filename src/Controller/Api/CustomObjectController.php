<?php

namespace App\Controller\Api;

use App\Annotation\ApiRoute;
use App\Dto\CustomObject_Dto;
use App\Dto\Dto;
use App\Dto\DtoFactory;
use App\Entity\Portal;
use App\Entity\User;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Model\CustomObjectField;
use App\Model\FieldCatalog;
use App\Model\Pagination\PaginationCollection;
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


/**
 * Class CustomObjectController
 * @package App\Controller\Api
 */
class CustomObjectController extends ApiController
{

    /**
     * Get Custom Objects
     *
     * Lists the custom objects (including system defined objects) in the platform
     *
     * @ApiRoute("/custom-objects", name="custom_objects", methods={"GET"}, versions={"v1"}, scopes={"private"})
     *
     * @SWG\Get(
     *     description=Api::DESCRIPTION,
     *     produces={"application/json"}
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the custom objects (including shipped objects) in the platform",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="total", type="integer", example=135, description="Total count of all items."),
     *          @SWG\Property(property="count", type="integer", example=12, description="Count of items returned in response."),
     *          @SWG\Property(property="data", type="array", @Model(type=CustomObject_Dto::class, groups={Dto::GROUP_DEFAULT})),
     *          @SWG\Property(property="_links", type="object",
     *              @SWG\Property(property="self", type="string", example="/api/v1/private/custom-objects?page=3"),
     *              @SWG\Property(property="first", type="string", example="/api/v1/private/custom-objects?page=1"),
     *              @SWG\Property(property="last", type="string", example="/api/v1/private/custom-objects?page=8"),
     *              @SWG\Property(property="next", type="string", example="/api/v1/private/custom-objects?page=4"),
     *              @SWG\Property(property="prev", type="string", example="/api/v1/private/custom-objects?page=2")
     *          )
     *     )
     * )
     *
     * @SWG\Response(
     *     response=400,
     *     description="Error: Bad Request",
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
     *          response=403,
     *          description="Forbidden",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="No Permission to Access.")
     *          )
     *     )
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
     * @SWG\Tag(name="Custom Objects")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     * @throws \App\Exception\DataTransformerNotFoundException
     */
    public function index(Request $request)
    {
        // todo need to add query parameter options to return an objects properties and property groups as well.
        // todo do this on other requests as well

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        /** @var Portal $portal */
        $portal = $this->portalResolver->resolve();

        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', null);

        $qb = $this->customObjectRepository->findAllQueryBuilder($portal);

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setAllowOutOfRangePages(true);

        if($limit) {
            $pagerfanta->setMaxPerPage($limit);
        }

        $pagerfanta->setCurrentPage($page);

        $customObjects = [];
        foreach ($pagerfanta->getCurrentPageResults() as $result) {
            $customObjects[] = $result;
        }

        // we need to proxy all these custom objects through our transformer.
        $dto = $this->dtoFactory->create(DtoFactory::CUSTOM_OBJECT, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        $dtos = [];
        foreach ($customObjects as $customObject) {
            /** @var Dto $dto */
            $dtos[] = $dataTransformer->transform($customObject);
        }

        $paginationCollection = new PaginationCollection($dtos, $pagerfanta);

        $json = $this->serializer->serialize($paginationCollection, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Custom Object
     *
     * Creates a user defined custom object in the platform.
     *
     * @ApiRoute("/custom-objects/new", name="custom_object_new", methods={"POST"}, versions={"v1"}, scopes={"private"})
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
     *         @Model(type=CustomObject_Dto::class, groups={Dto::GROUP_CREATE})
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
     *          description="Returns a newly created custom object",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=CustomObject_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     *         description="Error: Bad Request",
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
     *
     *    @SWG\Response(
     *          response=403,
     *          description="Forbidden",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="No Permission to Access.")
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
     * @SWG\Tag(name="Custom Objects")
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
     * Fetch a Custom Object.
     *
     * Fetches a custom object or system defined object in the platform.
     *
     * @ApiRoute("/custom-objects/{id}/view", name="custom_object_view", methods={"GET"}, versions={"v1"}, scopes={"private"})
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
     *          description="Returns the desired custom object.",
     *          @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="data", ref=@Model(type=CustomObject_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     *    @SWG\Response(
     *          response=403,
     *          description="Forbidden",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="No Permission to Access.")
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
     * @SWG\Tag(name="Custom Objects")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param CustomObject $customObject
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function view(Request $request, CustomObject $customObject) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        $dto = $this->dtoFactory->create(DtoFactory::CUSTOM_OBJECT, $version, true);

        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());

        $json = $this->serializer->serialize(
            $dataTransformer->transform($customObject),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * Edit a Custom Object
     *
     * Edit a user defined custom object in the platform.
     *
     * @ApiRoute("/custom-objects/{id}/edit", name="custom_object_edit", methods={"PATCH"}, versions={"v1"}, scopes={"private"})
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
     *         @Model(type=CustomObject_Dto::class, groups={Dto::GROUP_UPDATE})
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
     *          description="Returns the updated custom object",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=CustomObject_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     *
     *    @SWG\Response(
     *          response=403,
     *          description="Forbidden",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="No Permission to Access.")
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
     * @SWG\Tag(name="Custom Objects")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param CustomObject $customObject
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function edit(Request $request, CustomObject $customObject) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        // First transform the custom object to our DTO
        /** @var Dto $dto */
        $dto = $this->dtoFactory->create(DtoFactory::CUSTOM_OBJECT, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        /** @var Dto $dto */
        $dto = $dataTransformer->transform($customObject);

        // populate the DTO with the request content
        /** @var Dto $dto */
        $dto = $this->serializer->deserialize(
            $request->getContent(),
            $this->dtoFactory->create(DtoFactory::CUSTOM_OBJECT, $version),
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
        /** @var CustomObject $customObject */
        $customObject = $dataTransformer->reverseTransform($dto);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($customObject),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * Delete a Custom Object
     *
     * Delete a user defined custom object in the platform.
     *
     * @ApiRoute("/custom-objects/{id}/delete", name="custom_object_delete", methods={"DELETE"}, versions={"v1"}, scopes={"private"})
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
     *          description="Returns the deleted custom object",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=CustomObject_Dto::class, groups={Dto::GROUP_DEFAULT}))
     *          )
     *     ),
     *
     *     @SWG\Response(
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
     *    @SWG\Response(
     *          response=403,
     *          description="Forbidden",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="No Permission to Access.")
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
     * @SWG\Tag(name="Custom Objects")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param CustomObject $customObject
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DataTransformerNotFoundException
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function delete(Request $request, CustomObject $customObject) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

        // First transform the custom object to our DTO
        /** @var Dto $dto */
        $dto = $this->dtoFactory->create(DtoFactory::CUSTOM_OBJECT, $version, true);
        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->dataTransformerFactory->get($dto->getDataTransformer());
        /** @var Dto $dto */
        $dto = $dataTransformer->transform($customObject);

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

        $this->entityManager->remove($customObject);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dto,
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/{internalIdentifier}/api/objects", name="get_custom_objects", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param Request $request
     * @return Response
     */
    public function getCustomObjectsAction(Portal $portal, Request $request) {

        $customObjects = $this->customObjectRepository->findBy([
            'portal' => $portal->getId()
        ]);

        $payload = [];
        $payload['custom_objects'] = [];

        foreach($customObjects as $customObject) {
            $json = $this->serializer->serialize($customObject, 'json', ['groups' => ['CUSTOM_OBJECTS_FOR_FILTER']]);

            $payload['custom_objects'][] = json_decode($json, true);
        }

        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ],  Response::HTTP_OK);

        return $response;
    }

    /**
     * @Route("/{internalIdentifier}/api/objects/{internalName}/connectable", name="get_connectable_objects", methods={"GET"}, options = { "expose" = true })
     * @param CustomObject $customObject
     * @param Portal $portal
     * @param Request $request
     * @return Response
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getConnectableObjectsAction(CustomObject $customObject, Portal $portal, Request $request) {
        $customObjects = $this->customObjectRepository->getConnectableObjects($customObject);
        $payload = [];
        $payload['custom_objects'] = $customObjects;
        $response = new JsonResponse([
            'success' => true,
            'data'  => $payload,
        ],  Response::HTTP_OK);
        return $response;
    }


    /**
     * This doesn't return a list of all possible merge tags. Just 1 level deep.
     * The user can go deeper with the extraction depending on the data they want to use
     *
     * @Route("/{internalIdentifier}/api/objects/{internalName}/merge-tags", name="get_merge_tags", methods={"GET"}, options = { "expose" = true })
     * @param Portal $portal
     * @param CustomObject $customObject
     * @param Request $request
     * @return JsonResponse
     */
    public function getMergeTags(Portal $portal, CustomObject $customObject, Request $request) {

        $mergeTags = [];
        foreach($customObject->getProperties() as $property) {
            $parentTag = $property->getInternalName();
            if($property->getFieldType() === FieldCatalog::CUSTOM_OBJECT) {
                /** @var CustomObjectField $customObjectField */
                $customObjectField = $property->getField();
                foreach($customObjectField->getCustomObject()->getProperties() as $property) {
                    $mergeTags[] = sprintf("{%s.%s}", $parentTag, $property->getInternalName());
                }
            } else {
                $mergeTags[] = sprintf("{%s}", $parentTag);
            }
        }

        return new JsonResponse([
            'success' => true,
            'data'  => $mergeTags
        ], Response::HTTP_OK);
    }
}