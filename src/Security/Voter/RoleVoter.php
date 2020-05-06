<?php

namespace App\Security\Voter;

use App\Dto\DtoFactory;
use App\Entity\GmailAttachment;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\AclEntryRepository;
use App\Repository\AclLockRepository;
use App\Security\Auth\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RoleVoter extends Voter
{
    const CREATE = 'create';
    const READ = 'read';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const ALL = 'all';

    /**
     * @var AclEntryRepository
     */
    private $aclEntryRepository;

    /**
     * @var PermissionManager
     */
    private $permissionManager;

    /**
     * @var AclLockRepository
     */
    private $aclLockRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * RoleVoter constructor.
     * @param AclEntryRepository $aclEntryRepository
     * @param PermissionManager $permissionManager
     * @param AclLockRepository $aclLockRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        AclEntryRepository $aclEntryRepository,
        PermissionManager $permissionManager,
        AclLockRepository $aclLockRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->aclEntryRepository = $aclEntryRepository;
        $this->permissionManager = $permissionManager;
        $this->aclLockRepository = $aclLockRepository;
        $this->entityManager = $entityManager;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::CREATE, self::READ, self::UPDATE, self::DELETE, self::ALL])) {
            return false;
        }

        if (!$subject instanceof Role) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        $aclLock = $this->aclLockRepository->findOneBy([
            'objectIdentifier' => $subject->getId(),
            'classType' => DtoFactory::ROLE
        ]);

        // If the object does not have a lock than allow access
        // without even checking acl entries (permissions)
        if(!$aclLock) {
            return true;
        }

        $aclEntries = $this->aclEntryRepository->findBy([
            'objectIdentifier' => $subject->getId(),
            'classType' => DtoFactory::ROLE
        ]);

        // If there are no acl entries for a locked object then return false
        // Essentially this means nobody in the whole platform can access it
        if(empty($aclEntries)) {
            return false;
        }

        $validAclEntries = [];
        foreach($aclEntries as $aclEntry) {
            $securityIdentityArray = explode("-", $aclEntry->getSecurityIdentity());

            if(empty($securityIdentityArray[0]) || empty($securityIdentityArray[1])) {
                continue;
            }

            $securityIdentityClassName = $securityIdentityArray[0];

            try {
                $securityIdentityRepository = $this->entityManager->getRepository($securityIdentityClassName);
            } catch (\Exception $exception) {
                continue;
            }

            $securityIdentityId = $securityIdentityArray[1];
            $object = $securityIdentityRepository->find($securityIdentityId);

            if($object instanceof User) {
                if($user->getId() === $object->getId()) {
                    $validAclEntries[] = $aclEntry;
                }
            } elseif ($object instanceof Role) {
                if($user->hasRole($object)) {
                    $validAclEntries[] = $aclEntry;
                }
            }
        }

        if(empty($validAclEntries)) {
            return false;
        }

        $bit = $this->permissionManager->resolveMasks([$attribute]);

        return $this->permissionManager->isAuthorized($bit, $validAclEntries);
    }

}