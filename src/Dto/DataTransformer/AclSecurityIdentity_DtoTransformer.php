<?php

namespace App\Dto\DataTransformer;

use App\Dto\AclEntry_Dto;
use App\Dto\AclSecurityIdentity_Dto;
use App\Entity\AclEntry;
use App\Entity\AclSecurityIdentity;
use App\Repository\AclEntryRepository;
use App\Repository\AclSecurityIdentityRepository;
use App\Security\Auth\PermissionManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AclSecurityIdentity_DtoTransformer implements DataTransformerInterface
{
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
     * AclSecurityIdentity_DtoTransformer constructor.
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


    public function transform($object)
    {
        if(!$object instanceof AclSecurityIdentity) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", AclSecurityIdentity::class));
        }

        $dto = new AclSecurityIdentity_Dto();
        $dto->setSecurityIdentity($object->getIdentity());
        foreach($object->getAclEntries() as $aclEntry) {
            $aclEntryDto = new AclEntry_Dto();
            $aclEntryDto->setAttributeIdentifier($aclEntry->getAttributeIdentifier())
                ->setObjectIdentifier($aclEntry->getObjectIdentifier())
                ->setMask($aclEntry->getMask());

            $dto->addAclEntry($aclEntryDto);
        }

        return $dto;
    }

    public function reverseTransform($dto)
    {
        if(!$dto instanceof AclSecurityIdentity_Dto) {
            throw new TransformationFailedException(sprintf("Dto must be an instance of %s", AclSecurityIdentity_Dto::class));
        }

        $securityIdentity = $this->aclSecurityIdentityRepository->findOneBy([
           'identity' => $dto->getSecurityIdentity()
        ]);

        if(!$securityIdentity) {
            $securityIdentity = new AclSecurityIdentity();
            $securityIdentity->setIdentity($dto->getSecurityIdentity());
        } else {
            // clear all the acl entries associated with this identity so you can re-add the new ones
            foreach($securityIdentity->getAclEntries() as $aclEntry) {
                $this->entityManager->remove($aclEntry);
            }
            $this->entityManager->flush();
        }

        foreach($dto->getAclEntries() as $aclEntryDto) {
            $aclEntry = new AclEntry();
            $aclEntry->setMask($aclEntryDto->getMask());
            $grants = $this->permissionManager->resolveGrants($aclEntry);
            $aclEntry->setGrantingStrategy($grants);
            $aclEntry->setObjectIdentifier($aclEntryDto->getObjectIdentifier());
            $aclEntry->setAttributeIdentifier($aclEntryDto->getAttributeIdentifier());
            $securityIdentity->addAclEntry($aclEntry);
        }

        return $securityIdentity;
    }
}