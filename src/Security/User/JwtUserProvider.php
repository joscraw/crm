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

        $user->addRoles($this->getRoles($jwt));

        return $user;

    }

    public function loadUserByUsername($username)
    {
        $name = "Josh";
        throw new NotImplementedException('method not implemented');
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $user = $this->userRepository->findOneBy([
            'sub' => $user->getSub()
        ]);

        return $user;

    }

    public function supportsClass($class)
    {
        return $class === User::class;
    }

    /**
     * Returns the roles for the user.
     *
     * @param array $jwt
     * @return array
     */
    private function getRoles(array $jwt)
    {
        return array_merge(
            [
                'ROLE_JWT_AUTHENTICATED',
            ],
            $this->getScopesFromJwtAsRoles($jwt)
        );
    }

    /**
     * Returns the scopes from the JSON Web Token as Symfony roles prefixed with 'ROLE_JWT_SCOPE_'.
     *
     * @param array $jwt
     * @return array
     */
    private function getScopesFromJwtAsRoles(array $jwt)
    {
        if (!isset($jwt['scope'])) {
            return [];
        }

        $scopes = explode(' ', $jwt['scope']);
        $roles = array_map(
            function ($scope) {
                $roleSuffix = strtoupper(str_replace([':', '-'], '_', $scope));

                return sprintf('ROLE_JWT_SCOPE_%s', $roleSuffix);
            },
            $scopes
        );

        return $roles;
    }
}