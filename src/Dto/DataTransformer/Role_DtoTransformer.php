<?php

namespace App\Dto\DataTransformer;

use App\Dto\CustomObject_Dto;
use App\Dto\Role_Dto;
use App\Entity\CustomObject;
use App\Entity\Role;
use App\Repository\CustomObjectRepository;
use App\Repository\PortalRepository;
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
     * @var PortalRepository
     */
    private $portalRepository;

    /**
     * Role_DtoTransformer constructor.
     * @param RoleRepository $roleRepository
     * @param PortalRepository $portalRepository
     */
    public function __construct(RoleRepository $roleRepository, PortalRepository $portalRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->portalRepository = $portalRepository;
    }

    public function transform($role)
    {
        if(!$role instanceof Role) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", Role::class));
        }

        $dto = new Role_Dto();

        $dto->setId($role->getId())
            ->setName($role->getName())
            ->setDescription($role->getDescription());

        if($portal = $role->getPortal()) {
            $dto->setInternalIdentifier($portal->getInternalIdentifier());
        }

        return $dto;
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

        if($internalIdentifier = $dto->getInternalIdentifier()) {
            $portal = $this->portalRepository->findOneBy([
                'internalIdentifier' => $internalIdentifier
            ]);

            if($portal) {
                $role->setPortal($portal);
            }
        }

        $role->setName($dto->getName());

        return $role;
    }
}