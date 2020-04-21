<?php

namespace App\Security;

use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use App\Repository\UserRepository;
use App\Security\User\JWTUserProviderInterface;
use App\Utils\ServiceHelper;
use Auth0\SDK\Exception\CoreException;
use Auth0\SDK\Exception\InvalidTokenException;
use Auth0\SDK\Helpers\Tokens\TokenVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use App\Entity\User;
use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\SymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\IdTokenVerifier;

class JWTGuardAuthenticator extends AbstractGuardAuthenticator
{

    /**
     * @var Auth0Service
     */
    private $auth0Service;

    /**
     * JWTGuardAuthenticator constructor.
     * @param Auth0Service $auth0Service
     */
    public function __construct(Auth0Service $auth0Service)
    {
        $this->auth0Service = $auth0Service;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization') &&
            strpos($request->headers->get('Authorization'), 'Bearer') === 0;
    }

    /**
     * Retrieves the authentication credentials from the 'Authorization' request header.
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        // Removes the 'Bearer ' part from the Authorization header value.
        $jwt = str_replace('Bearer ', '', $request->headers->get('Authorization', ''));
        if (empty($jwt)) {
            return null;
        }

        return [
            'jwt' => $jwt,
        ];
    }

    /**
     * Returns a user based on the information inside the JSON Web Token depending on the implementation
     * of the configured user provider.
     *
     * When the user provider does not implement the JWTUserProviderInterface it will attempt to load
     * the user by username with the 'sub' (subject) claim of the JSON Web Token.
     *
     * @param array                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            return null;
        }

        try {
            $jwt = $this->auth0Service->decodeJWT($credentials['jwt']);
        } catch (CoreException $exception) {
            return null;
        }

        $jwt['token'] = $credentials['jwt'];

        if ($userProvider instanceof JWTUserProviderInterface) {
            return $userProvider->loadUserByJWT($jwt);
        }

        return $userProvider->loadUserByUsername($jwt['sub']);
    }

    /**
     * Returns true when the provided JSON Web Token successfully decodes and validates.
     *
     * @param array         $credentials
     * @param UserInterface $user
     *
     * @return bool
     *
     * @throws AuthenticationException when decoding and/or validation of the JSON Web Token fails
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if(!$user) {
            throw new ApiException(new ApiErrorResponse("Authorization has been refused for those credentials.",
                null,
                [],
                Response::HTTP_UNAUTHORIZED
            ));
        }

        return true;
    }

    /**
     * Returns nothing to continue the request when authenticated.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    /**
     * Returns the 'Authentication failed' response.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new ApiErrorResponse($exception->getMessage(),
            null,
            [],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * @param Request                 $request
     * @param AuthenticationException $authenticationException
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authenticationException = null)
    {
        return new ApiErrorResponse('Authentication Required',
            null,
            [],
            Response::HTTP_UNAUTHORIZED
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

}
