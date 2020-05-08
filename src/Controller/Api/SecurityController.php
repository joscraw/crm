<?php

namespace App\Controller\Api;

use App\Annotation\ApiRoute;
use App\Dto\Dto;
use App\Dto\DtoFactory;
use App\Entity\User;
use App\Http\ApiErrorResponse;
use App\Http\ApiResponse;
use App\Security\Auth\PermissionManager;
use App\Utils\ServiceHelper;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use App\Http\Api;
use App\Dto\SignUp_Dto;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;


/**
 * Class SecurityController
 * @package App\Controller\Api
 */
class SecurityController extends ApiController
{

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
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="username", type="string", example="joshcrawmer4@yahoo.com"),
     *              @SWG\Property(property="password", type="string", example="A54dWinwjBOm7M&k20rJ")
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
     *          response=201,
     *          description="Returns newly created access token",
     *           @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="access_token", type="string", example="alkfsd8782ljkfklsjfajkl2lsjf........"),
     *              @SWG\Property(property="expires_in", type="integer", example=86400),
     *              @SWG\Property(property="id_token", type="string", example="weuor82ob82nfdskjfo23fdsfsfds899........"),
     *              @SWG\Property(property="scope", type="string", example="openid profile email"),
     *              @SWG\Property(property="token_type", type="string", example="Bearer")
     *          )
     *     ),
     *
     *
     *     @SWG\Response(
     *          response=400,
     *          description="Error: Bad Request",
     *          @SWG\Schema(
     *              type="object",
     *              format="json",
     *              @SWG\Property(property="message", type="string", example="Bad request. Unexpected json parameters.")
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
     * @SWG\Tag(name="Security")
     *
     * @param Request $request
     * @param $auth0ClientId
     * @param $auth0ClientSecret
     * @param $auth0Audience
     * @return ApiErrorResponse|ApiResponse
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Symfony\Component\Config\Exception\LoaderLoadException
     */
    public function newToken(Request $request, $auth0ClientId, $auth0ClientSecret, $auth0Audience) {

        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $cache = new FilesystemAdapter();

        $key = md5($username) . '_auth0_user_access_token';
        $data = $cache->get($key, function (ItemInterface $item) use($username, $password, $auth0ClientId, $auth0ClientSecret, $auth0Audience) {
            // auth0 setting for expiration is 86400 seconds for access tokens issued by the /token endpoint.
            // keep an eye on this if you notice it expiring before this time and just adjust the seconds down here
            $item->expiresAfter(86400);

            $httpClient = HttpClient::create();

            $data = array(
                'grant_type' => 'password',
                'client_id' => $auth0ClientId,
                'client_secret' => $auth0ClientSecret,
                'username' => $username,
                'password' => $password,
                'scope' => 'openid profile email',
                'audience' => $auth0Audience
            );

            $response = $httpClient->request('POST', 'https://crm-development.auth0.com/oauth/token', [
                'json' => $data,
            ]);

            $data = $response->toArray();

            return $data;
        });

        return new ApiResponse(null, $data, Response::HTTP_CREATED, []);
    }

    /**
     * Sign up a user
     *
     *
     * @ApiRoute("/sign-up", name="sign_up", methods={"POST"}, versions={"v1"}, scopes={"private", "marketing"})
     *
     * @SWG\Post(
     *     description=Api::SECURITY_CONTROLLER_SIGN_UP,
     *     consumes={"application/json"},
     *     produces={"application/json"},
     *
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         required=true,
     *         description="JSON payload",
     *         format="application/json",
     *         @Model(type=SignUp_Dto::class, groups={Dto::GROUP_CREATE})
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
     *          description="Returns a newly singed up user",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="data", ref=@Model(type=SignUp_Dto::class, groups={Dto::GROUP_DEFAULT}))
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
     * @throws \Exception
     */
    public function signUp(Request $request) {

        $version = $request->headers->get('X-Accept-Version');

        $dto = $this->dtoFactory->create(DtoFactory::SIGN_UP, $version);

        /** @var SignUp_Dto $dto */
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

        $data = [
            'email' => $dto->getEmail(),
            'name' => $dto->getFullName(),
            'password' => $dto->getPassword()
        ];

        // if a portal identifier is passed up we need to limit scope to that given portal.
        // if one is not passed up then grant access to all portals in the app.

        //$response = $this->auth0MgmtApi->searchUsersByEmail($email);

        // todo add validation to this endpoint.

        // todo serialize this back into a json response
        $response = $this->auth0MgmtApi->createUser($data);

        $user = (new User())
            ->setEmail($dto->getEmail())
            ->setFirstName($dto->getFirstName())
            ->setLastName($dto->getLastName())
            ->setSub($response['user_id']);


        if($dto->getInternalIdentifier()) {
            $portal = $this->portalRepository->findOneBy([
                'internalIdentifier' => $dto->getInternalIdentifier()
            ]);
            $role = $this->roleRepository->findOneBy([
                'portal' => $portal,
                'name' => 'ROLE_SUPER_ADMIN'
            ]);
            $user->addCustomRole($role);
        } else {
            $role = $this->roleRepository->findOneBy([
                'portal' => null,
                'name' => 'ROLE_SUPER_ADMIN'
            ]);
            $user->addCustomRole($role);
        }


        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // todo we need to return the SignUpDto here? Or the userDto.
        // todo I'm really not sure. I need to think this one through.
        // todo can we add the roles and permissions to auth0? Or do we not.
        // todo I think we add it to the response here right?
        $json = $this->serializer->serialize(
            $dto,
            'json',
            ['groups' => [Dto::GROUP_DEFAULT]]);

        return new ApiResponse(null, $json,Response::HTTP_CREATED, [
            'Location' => !empty(json_decode($json, true)['_links']['view']) ? json_decode($json, true)['_links']['view'] : ''
        ], true);
    }
}