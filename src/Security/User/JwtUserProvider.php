<?php

namespace App\Security\User;

use App\Exception\ApiException;
use App\Http\ApiErrorResponse;
use App\Repository\UserRepository;
use App\Security\Auth0Service;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

class JwtUserProvider implements JWTUserProviderInterface
{
    protected $auth0Service;
    private $userRepository;

    public function __construct(Auth0Service $auth0Service, UserRepository $userRepository) {
        $this->auth0Service = $auth0Service;
        $this->userRepository = $userRepository;
    }

    public function loadUserByJWT($jwt) {

        $user = $this->userRepository->findOneBy([
            'sub' => $jwt['sub']
        ]);

        if(!$user) {
            throw new ApiException(new ApiErrorResponse("Authorization has been refused for those credentials.",
                null,
                [],
                Response::HTTP_UNAUTHORIZED
            ));
        }

        if(isset($jwt['token'])) {
            $user->setToken($jwt['token']);
        }

        // Most of the secured api routes require this role.
        $user->addRole('ROLE_ADMIN_USER');

        return $user;

    }

    public function loadUserByUsername($username)
    {
        throw new NotImplementedException('method not implemented');
    }

    public function refreshUser(UserInterface $user)
    {
        return $user;
    }

    public function supportsClass($class)
    {
        return $class === User::class;
    }
}