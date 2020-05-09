<?php

namespace App\Security;

use App\Entity\Role;
use App\Entity\User;
use App\Security\Auth\PermissionManager;

abstract class AbstractACL
{

    public function getObjectIdentifier() {
        return static::class;
    }

    public function getSecurityIdentities() {
        return [
          User::class,
          Role::class,
        ];
    }

    public function getMasks() {
        return [
            PermissionManager::MASK_CREATE,
            PermissionManager::MASK_READ,
            PermissionManager::MASK_UPDATE,
            PermissionManager::MASK_DELETE,
            PermissionManager::MASK_DENY_ALL,
            PermissionManager::MASK_ENABLED
        ];
    }

    public function getAttributeIdentifiers() {
        try {
            return PermissionManager::load();
        } catch (\Exception $exception) {
            return [];
        }
    }
}