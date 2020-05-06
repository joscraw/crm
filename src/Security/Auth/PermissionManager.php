<?php

namespace App\Security\Auth;

use App\Entity\AclEntry;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Entity\Property;
use App\Entity\PropertyGroup;
use App\Entity\Record;
use App\Entity\Role;
use App\Repository\PermissionRepository;

class PermissionManager
{
    const MASK_CREATE = 1;
    const MASK_READ = 2;
    const MASK_UPDATE = 4;
    const MASK_DELETE = 8;
    const MASK_ALL = 15;

    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * PermissionManager constructor.
     * @param PermissionRepository $permissionRepository
     */
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    /**
     * Resolves the grants for a set of aclEntries
     *
     * @param array $aclEntries
     * @param bool $stringify
     * @return array
     */
    public function resolveGrants(array $aclEntries, $stringify = false) {
        $result = [];
        /** @var AclEntry $aclEntry */
        foreach($aclEntries as $key => $aclEntry) {
            $row = [];
            if (($aclEntry->getMask() & self::MASK_CREATE) == self::MASK_CREATE) $row[] = 'create';
            if (($aclEntry->getMask() & self::MASK_READ) == self::MASK_READ) $row[] = 'read';
            if (($aclEntry->getMask() & self::MASK_UPDATE) == self::MASK_UPDATE) $row[] = 'update';
            if (($aclEntry->getMask() & self::MASK_DELETE) == self::MASK_DELETE) $row[] = 'delete';
            if (($aclEntry->getMask() & self::MASK_ALL) == self::MASK_ALL) $row[] = 'all';
            $result[$aclEntry->getId() || $key] = $stringify ? implode(", ", $row) : $row;
        }
        return $result;
    }

    /**
     * Returns the mask for a set of grants
     *
     * @param array|null $grants
     * @return int
     *
     */
    public function resolveMasks(?array $grants) {
        $bit = 0;
        if ($grants != null && count($grants)) {
            if (in_array('create', $grants)) $bit |= self::MASK_CREATE;
            if (in_array('read', $grants)) $bit |= self::MASK_READ;
            if (in_array('update', $grants)) $bit |= self::MASK_UPDATE;
            if (in_array('delete', $grants)) $bit |= self::MASK_DELETE;
            if (in_array('all', $grants)) $bit |= self::MASK_ALL;
        }

        return $bit;
    }

    /**
     * Determines whether or not a set of aclEntries are valid for a given bit
     * @param int $bit
     * @param array $aclEntries
     * @return bool
     */
    public function isAuthorized(int $bit, array $aclEntries): bool {

        /** @var AclEntry $aclEntry */
        foreach($aclEntries as $aclEntry) {

            if(($bit & $aclEntry->getMask()) == $bit) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Portal $portal
     * @return Role
     */
    public function configureSuperAdminRole(?Portal $portal = null): Role {

        $role = new Role();
        $role->setName('ROLE_SUPER_ADMIN')
            ->setDescription('Super Admin Role');

        if($portal) {
            $role->setPortal($portal);
        }

        $permissions = $this->permissionRepository->findAll();
        foreach($permissions as $permission) {
            $role->addPermission($permission);
        }

        return $role;
    }

}