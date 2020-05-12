<?php

namespace App\Security\Voter;

use App\Dto\DtoFactory;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\AclEntryRepository;
use App\Repository\AclSecurityIdentityRepository;
use App\Security\Auth\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class PropertyVoter extends Voter
{

    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const ENABLED = 'enabled';

    /**
     * @var AclEntryRepository
     */
    private $aclEntryRepository;

    /**
     * @var AclSecurityIdentityRepository
     */
    private $aclSecurityIdentityRepository;

    /**
     * @var PermissionManager
     */
    private $permissionManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RoleVoter constructor.
     * @param AclEntryRepository $aclEntryRepository
     * @param AclSecurityIdentityRepository $aclSecurityIdentityRepository
     * @param PermissionManager $permissionManager
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        AclEntryRepository $aclEntryRepository,
        AclSecurityIdentityRepository $aclSecurityIdentityRepository,
        PermissionManager $permissionManager,
        EntityManagerInterface $entityManager
    ) {
        $this->aclEntryRepository = $aclEntryRepository;
        $this->aclSecurityIdentityRepository = $aclSecurityIdentityRepository;
        $this->permissionManager = $permissionManager;
        $this->entityManager = $entityManager;
    }

    protected function supports($attribute, $subject)
    {

        $name = "Josh";
        return true;
        if (!in_array($attribute, [
            self::CREATE,
            self::READ,
            self::UPDATE,
            self::DELETE,
            self::ENABLED
        ])) {
            return false;
        }

        /*
        if (!$subject instanceof Role) {
            return false;
        }
        */

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {

        $user = $token->getUser();

        return false;

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        // If the user does not have any roles don't give them access
        // todo if we have permissions live on the user as well then we need
        // todo to pull permissions of the user as well
        if(count($user->getRoles()) === 0) {
            return false;
        }

        // todo these are just the role security identities
        // todo might be adding user security identities as well
        $securityIdentities = $user->getCustomRoles()->map(function(Role $role) {
            return $role::getObjectIdentifier() . '-' . $role->getId();
        });

        // todo I don't think we need to loop through the roles.
        // todo we can pass in an array of roles and do WHERE IN()
        if(is_string($subject)) {
            $aclEntries = $this->aclEntryRepository->findBySecurityIdentitiesAndObjectIdentifier(
                $securityIdentities->toArray(),
                $subject
            );

            // If there are no acl entries for a locked object then return false
            // Essentially this means nobody in the whole platform can access it
            if(empty($aclEntries)) {
                return false;
            }

            $bit = $this->permissionManager->resolveMasks([$attribute]);

            return $this->permissionManager->isAuthorized($bit, $aclEntries);
        } else if (is_object($subject)) {

            // todo handle object specific?

            return false;

        }

    }

}