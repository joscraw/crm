<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface JWTUserProviderInterface extends UserProviderInterface
{
    /**
     * Loads the user for the given decoded JWT.
     *
     * This method must throw JWTInfoNotFoundException if the user is not
     * found.
     *
     * @param array $jwt The decoded Json Web Token
     *
     * @return UserInterface
     *
     * @throws AuthenticationException if the user is not found
     */
    public function loadUserByJWT($jwt);
}