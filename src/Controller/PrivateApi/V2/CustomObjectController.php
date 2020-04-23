<?php

namespace App\Controller\PrivateApi\V2;

use App\Entity\User;
use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Utils\ServiceHelper;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Entity\CustomObject;
use Nelmio\ApiDocBundle\Annotation\Security;
use App\Controller\PrivateApi\V1\CustomObjectController as CustomObjectController_V1;

/**
 * Class AlbumController
 * @package App\Controller\PrivateApi
 *
 * @Route("/api/private")
 *
 */
class CustomObjectController extends CustomObjectController_V1
{
    use ServiceHelper;

    /**
     * Get Custom Objects
     *
     * Lists the custom objects (including system defined objects) in the platform
     *
     * @Route("/v2/custom-objects", name="private_api_v2_custom_objects", methods={"GET"})
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
        return parent::index($request);
    }

}