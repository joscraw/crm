<?php

namespace App\AuthorizationHandler;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


/**
 * Class PermissionAuthorizationHandler
 * @package App\AuthorizationHandler
 */
class PermissionAuthorizationHandler
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * PermissionAuthorizationHandler constructor.
     * @param EntityManagerInterface $entityManager
     * @param RoleRepository $roleRepository
     */
    public function __construct(EntityManagerInterface $entityManager, RoleRepository $roleRepository)
    {
        $this->entityManager = $entityManager;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param User $user
     * @param $permission
     * @param string $permissionType
     * @return bool
     */
    public function isAuthorized(User $user, $permission, $permissionType = Role::OBJECT_PERMISSION)
    {

        if($user->hasPermission($permission, $permissionType)) {
            return true;
        }

        return false;

    }


}