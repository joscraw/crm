<?php

namespace App\Dto\DataTransformer;

use App\Dto\AclLock_Dto;
use App\Entity\AclLock;
use App\Repository\AclLockRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AclLock_DtoTransformer implements DataTransformerInterface
{
    /**
     * @var AclLockRepository
     */
    private $aclLockRepository;

    /**
     * AclLock_DtoTransformer constructor.
     * @param AclLockRepository $aclLockRepository
     */
    public function __construct(AclLockRepository $aclLockRepository)
    {
        $this->aclLockRepository = $aclLockRepository;
    }

    public function transform($object)
    {
        if(!$object instanceof AclLock) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", AclLock::class));
        }

        $dto = new AclLock_Dto();

        $dto->setId($object->getId())
            ->setObjectIdentifier($object->getObjectIdentifier())
            ->setClassType($object->getClassType())
            ->setFieldName($object->getFieldName());

        return $dto;
    }

    public function reverseTransform($dto)
    {
        if(!$dto instanceof AclLock_Dto) {
            throw new TransformationFailedException(sprintf("Dto must be an instance of %s", AclLock_Dto::class));
        }

        if($dto->getId()) {
            $aclLock = $this->aclLockRepository->find($dto->getId());
            if(!$aclLock) {
                throw new TransformationFailedException('AclLock with dto id not found');
            }
        } else {
            $aclLock = new AclLock();
        }

        $aclLock->setObjectIdentifier($dto->getObjectIdentifier())
            ->setClassType($dto->getClassType())
            ->setFieldName($dto->getFieldName());

        return $aclLock;
    }
}