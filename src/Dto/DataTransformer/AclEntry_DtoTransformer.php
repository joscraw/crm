<?php

namespace App\Dto\DataTransformer;

use App\Dto\AclEntry_Dto;
use App\Dto\Role_Dto;
use App\Entity\AclEntry;
use App\Entity\Role;
use App\Repository\AclEntryRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AclEntry_DtoTransformer implements DataTransformerInterface
{
    /**
     * @var AclEntryRepository
     */
    private $aclEntryRepository;

    /**
     * AclEntry_DtoTransformer constructor.
     * @param AclEntryRepository $aclEntryRepository
     */
    public function __construct(AclEntryRepository $aclEntryRepository)
    {
        $this->aclEntryRepository = $aclEntryRepository;
    }


    public function transform($object)
    {
        if(!$object instanceof AclEntry) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", AclEntry::class));
        }

        $dto = new AclEntry_Dto();

        $dto->setId($object->getId())
            ->setObjectIdentifier($object->getObjectIdentifier())
            ->setMask($object->getMask())
            ->setClassType($object->getClassType())
            ->setSecurityIdentity($object->getSecurityIdentity())
            ->setGranting($object->getGranting())
            ->setGrantingStrategy($object->getGrantingStrategy());

        return $dto;
    }

    public function reverseTransform($dto)
    {
        if(!$dto instanceof AclEntry_Dto) {
            throw new TransformationFailedException(sprintf("Dto must be an instance of %s", AclEntry_Dto::class));
        }

        if($dto->getId()) {
            $aclEntry = $this->aclEntryRepository->find($dto->getId());
            if(!$aclEntry) {
                throw new TransformationFailedException('AclEntry with dto id not found');
            }
        } else {
            $aclEntry = new AclEntry();
        }

        $aclEntry->setObjectIdentifier($dto->getObjectIdentifier())
            ->setMask($dto->getMask())
            ->setClassType($dto->getClassType())
            ->setSecurityIdentity($dto->getSecurityIdentity())
            ->setGranting($dto->getGranting());

        return $aclEntry;
    }
}