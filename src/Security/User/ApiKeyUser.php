<?php

namespace App\Security\User;

use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiKeyUser implements UserInterface, EquatableInterface
{
    private $roles;
    private $scopes;
    private $jwt;

    public function __construct($jwt, array $roles, array $scopes)
    {
        $this->roles = $roles;
        $this->jwt = $jwt;
        $this->scopes = $scopes;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getScopes()
    {
        return $this->scopes;
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return isset($this->jwt["id"]) ? $this->jwt["id"] : $this->jwt["name"];
    }

    public function eraseCredentials()
    {

    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof ApiKeyUser) {
            return false;
        }

        if ($this->getUsername() !== $user->getUsername()) {
            return false;
        }

        return true;
    }
}