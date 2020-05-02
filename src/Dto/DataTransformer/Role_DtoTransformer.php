<?php

namespace App\Dto\DataTransformer;

use App\Dto\CustomObject_Dto;
use App\Dto\Role_Dto;
use App\Entity\CustomObject;
use App\Entity\Role;
use App\Repository\CustomObjectRepository;
use App\Repository\RoleRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class Role_DtoTransformer implements DataTransformerInterface
{
    /**
     * @var RoleRepository
     */
    private $roleRepository;

    /**
     * CustomObject_DtoTransformer constructor.
     * @param RoleRepository $roleRepository
     */
    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function transform($role)
    {
        if(!$role instanceof Role) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", Role::class));
        }

        return (new Role_Dto())
            ->setId($role->getId())
            ->setName($role->getName())
            ->setPermissions($role->getPermissions());
    }
    public function reverseTransform($dto)
    {
        if(!$dto instanceof Role_Dto) {
            throw new TransformationFailedException(sprintf("Dto must be an instance of %s", Role_Dto::class));
        }

        if($dto->getId()) {
            $role = $this->roleRepository->find($dto->getId());
            if(!$role) {
                throw new TransformationFailedException('Role with dto id not found');
            }
        } else {
            $role = new Role();
        }

        $role->setName($dto->getName())
            ->setPermissions($dto->getPermissions());

        return $role;
    }
}