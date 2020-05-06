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

class ApiKeyUserProvider implements JWTUserProviderInterface
{
    protected $auth0Service;
    private $userRepository;

    public function __construct(Auth0Service $auth0Service, UserRepository $userRepository) {
        $this->auth0Service = $auth0Service;
        $this->userRepository = $userRepository;
    }

    public function loadUserByJWT($jwt) {

        $roles = array();
        $roles[] = 'ROLE_API_KEY_USER';

        return new ApiKeyUser($jwt, $roles, $jwt['scopes']);
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
        return $class === ApiKeyUser::class;
    }
}