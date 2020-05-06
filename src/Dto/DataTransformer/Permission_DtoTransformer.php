<?php

namespace App\Dto\DataTransformer;

use App\Dto\Permission_Dto;
use App\Entity\Permission;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class Permission_DtoTransformer implements DataTransformerInterface
{

    public function transform($permission)
    {
        if(!$permission instanceof Permission) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", Permission::class));
        }

        return (new Permission_Dto())
            ->setId($permission->getId())
            ->setScope($permission->getScope())
            ->setDescription($permission->getDescription());
    }

    public function reverseTransform($dto)
    {
       // do nothing
    }
}