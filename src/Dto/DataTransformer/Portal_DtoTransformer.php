<?php

namespace App\Dto\DataTransformer;

use App\Dto\CustomObject_Dto;
use App\Dto\Portal_Dto;
use App\Entity\CustomObject;
use App\Entity\Portal;
use App\Repository\CustomObjectRepository;
use App\Repository\PortalRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class Portal_DtoTransformer implements DataTransformerInterface
{

    /**
     * @var PortalRepository
     */
    private $portalRepository;

    /**
     * Portal_DtoTransformer constructor.
     * @param PortalRepository $portalRepository
     */
    public function __construct(PortalRepository $portalRepository)
    {
        $this->portalRepository = $portalRepository;
    }

    public function transform($object)
    {
        if(!$object instanceof Portal) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", Portal::class));
        }

        return (new Portal_Dto())
            ->setId($object->getId())
            ->setName($object->getName())
            ->setSystemDefined($object->getSystemDefined());
    }

    public function reverseTransform($dto)
    {
        if(!$dto instanceof Portal_Dto) {
            throw new TransformationFailedException(sprintf("Dto must be an instance of %s", Portal_Dto::class));
        }

        if($dto->getId()) {
            $object = $this->portalRepository->find($dto->getId());
            if(!$object) {
                throw new TransformationFailedException('Portal with dto id not found');
            }
        } else {
            $object = new Portal();
        }

        $object->setName($dto->getName())
            ->setSystemDefined($dto->getSystemDefined());

        return $object;
    }
}