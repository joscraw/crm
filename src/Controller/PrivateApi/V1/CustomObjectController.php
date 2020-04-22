<?php

namespace App\Controller\PrivateApi\V1;

use App\Entity\User;
use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Utils\ServiceHelper;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\CustomObject;
use Nelmio\ApiDocBundle\Annotation\Security;

/**
 * Class AlbumController
 * @package App\Controller\PrivateApi
 */
class CustomObjectController extends AbstractController
{
    use ServiceHelper;

    /**
     * Get Custom Objects
     *
     * Lists the custom objects (including system defined objects) in the platform
     *
     * @Route("/custom-objects", name="private_api_v1_custom_objects", methods={"GET"})
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
        $name = $request->attributes;
        return new ApiResponse(null, 'v1', Response::HTTP_OK, [], true);

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
     * @Route("/custom-objects/new", name="private_api_v1_custom_object_new", methods={"POST"})
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

}