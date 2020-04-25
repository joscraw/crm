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


/**
 * Class CustomObjectController
 * @package App\Controller\Api
 */
class CustomObjectController extends ApiController
{
    use ServiceHelper;

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
     *          @SWG\Property(property="data", type="array", @Model(type=CustomObject_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();

        $page = $request->query->get('page', 1);

        $qb = $this->customObjectRepository->findAllQueryBuilder($user->getPortal());

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setAllowOutOfRangePages(true);
        $pagerfanta->setMaxPerPage(10);
        $pagerfanta->setCurrentPage($page);
        $results = $pagerfanta->getCurrentPageResults();

        // todo wire in pagination collection logic, filtering, and total_count, etc here along with _links added
        //  to the response. Take a look at symfonycasts

        $json = $this->serializer->serialize($results, 'json', ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Custom Object
     *
     * Creates a user defined custom object in the platform.
     *
     * @ApiRoute("/custom-objects/new", name="custom_objects_new", methods={"POST"}, versions={"v1"}, scopes={"private"})
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
     *          response=200,
     *          description="Returns a newly created custom object",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=CustomObject_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     * @SWG\Tag(name="Custom Objects")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     */
    public function new(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $version = $request->headers->get('X-Accept-Version');

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

        // todo change this logic to use the new data transformer factory.

        /** @var DataTransformerInterface $dataTransformer */
        $dataTransformer = $this->get($dto->getDataTransformer());
        $customObject = $dataTransformer->reverseTransform($dto);
        $this->entityManager->persist($customObject);
        $this->entityManager->flush();

        $json = $this->serializer->serialize(
            $dataTransformer->transform($customObject),
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json, Response::HTTP_CREATED, [], true);
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
     * @SWG\Tag(name="Custom Objects")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @param CustomObject $customObject
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     * @throws \App\Exception\DataTransformerNotFoundException
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

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);
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