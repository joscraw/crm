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
 * Class SecurityController
 * @package App\Controller\Api
 */
class SecurityController extends ApiController
{
    use ServiceHelper;

    /**
     * Creates a New Access Token
     *
     * Creates a user defined custom object in the platform.
     *
     * @ApiRoute("/tokens/new", name="tokens_new", methods={"POST"}, versions={"v1"}, scopes={"public"})
     *
     * @SWG\Post(
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
     *          response=201,
     *          description="Returns newly created access token",
     *           @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="access_token", type="string", example="alkfsd8782ljkfklsjfajkl2lsjf........"),
     *              @SWG\Property(property="expires_in", type="integer", example=86400),
     *              @SWG\Property(property="token_type", type="string", example="Bearer")
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
     * @SWG\Tag(name="Security")
     *
     * @param Request $request
     * @return ApiErrorResponse|ApiResponse
     * @throws \App\Exception\DtoNotFoundException
     * @throws \ReflectionException
     * @throws \Auth0\SDK\Exception\ApiException
     */
    public function new(Request $request) {

        // todo this just may not work. We need a way to pass up an email and password
        //  otherwise this isn't going to work as we need a token that is user specific.
        //  There may be a way to do this in 2 separate calls. First to get authorization code
        //  and then access token? I need to look into this more.
        $result = $this->auth0Service->getAccessToken();

        return new ApiResponse(null, $result, Response::HTTP_CREATED, []);
    }
}