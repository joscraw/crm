<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\PortalRepository;
use App\Repository\UserRepository;
use App\Service\Auth0\Authenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Auth0\SDK\Helpers\JWKFetcher;
use Auth0\SDK\Helpers\Tokens\AsymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\SymmetricVerifier;
use Auth0\SDK\Helpers\Tokens\IdTokenVerifier;

class Auth0Authenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var CsrfTokenManagerInterface
     */
    private $csrfTokenManager;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var Authenticator
     */
    private $auth0Authenticator;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PortalRepository
     */
    private $portalRepository;


    public function __construct(EntityManagerInterface $entityManager, RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder, Authenticator $auth0Authenticator, UserRepository $userRepository, PortalRepository $portalRepository)
    {
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->auth0Authenticator = $auth0Authenticator;
        $this->userRepository = $userRepository;
        $this->portalRepository = $portalRepository;
    }

    public function supports(Request $request)
    {
        return 'auth0_callback' === $request->attributes->get('_route')
            && $request->isMethod('GET');
    }

    public function getCredentials(Request $request)
    {





        try {
            return $this->auth0Authenticator->getUser();
        } catch (\Exception $exception) {
            throw new CustomUserMessageAuthenticationException($exception->getMessage());
        }
    }

    // todo setup the database connection in the env file
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $this->userRepository->getByEmailAddress($credentials['email']);

        // todo this may not be needed if we have our own signup form
        // and don't use auth0's signup form
        if (!$user) {
            // Create and sync user profile info from auth0 here
            $systemDefinedPortal = $this->portalRepository->findOneBy([
                'systemDefined' => true
            ]);
            $user = new User();
            $user->setEmail($credentials['email']);
            $user->setAuth0UserId($credentials['sub']);
            $user->setEmailVerified($credentials['email_verified']);
            $user->setPortal($systemDefinedPortal);
            $user->addRole(User::ROLE_ADMIN_USER);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

/*        if(!$user->isActive()) {

            throw new CustomUserMessageAuthenticationException('Account has been disabled.');
        }

        if(!$user->isAdminUser()) {

            throw new CustomUserMessageAuthenticationException('You don\'t have proper permissions to use the CRM.');
        }*/

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // authentication is handled on the auth0 side so do nothing here
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
/*        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new JsonResponse(
                [
                    'targetPath' => $targetPath,
                    'success' => true,
                ], Response::HTTP_OK
            );
        }*/

        $targetPath = $this->router->generate('custom_object_settings', ['internalIdentifier' => $token->getUser()->getPortal()->getInternalIdentifier()]);

        return new RedirectResponse($targetPath);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {

        return new JsonResponse(
            [
                'success' => false,
                'message' => $exception->getMessage()
            ], Response::HTTP_OK
        );

    }

    protected function getLoginUrl()
    {
        return $this->router->generate('app_login');
    }
}
