<?php

namespace App\Controller\Api;

use App\Annotation\ApiRoute;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\User;
use App\Http\ApiResponse;
use App\Model\CustomObjectField;
use App\Model\FieldCatalog;
use App\Utils\ServiceHelper;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;


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
     *
     * @ApiRoute("/custom-objects", name="custom_objects", methods={"GET"}, versions={"v1", "v2"}, scopes={"private", "public"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the custom objects (including shipped objects) in the platform",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=CustomObject::class, groups={"v1"}))
     *     )
     * )
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

        $json = $this->serializer->serialize($results, 'json', ['groups' => ['v1']]);

        return new ApiResponse(null, $json, Response::HTTP_OK, [], true);
    }

    /**
     * Create a Custom Object
     *
     * Creates a user defined custom object in the platform.
     *
     * @SWG\Response(
     *     response=201,
     *     description="Creates a custom object in the platform",
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(ref=@Model(type=CustomObject::class, groups={"v1"}))
     *     )
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
     * @SWG\Tag(name="Custom Objects")
     * @Security(name="Bearer")
     *
     * @param Request $request
     * @return ApiResponse
     */
    public function new(Request $request) {

        /** @var User $user */
        $user = $this->getUser();

        $content = $request->getContent();

        $customObject = $this->serializer->deserialize($request->getContent(), CustomObject::class, 'json');

        return new ApiResponse(sprintf("Clinic successfully successfully created"), [
            'success' => true,
        ]);
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