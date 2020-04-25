<?php

namespace App\Dto\DataTransformer;

use App\Dto\CustomObject_Dto;
use App\Entity\CustomObject;
use App\Repository\CustomObjectRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CustomObject_DtoTransformer implements DataTransformerInterface
{
    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * CustomObject_DtoTransformer constructor.
     * @param CustomObjectRepository $customObjectRepository
     */
    public function __construct(CustomObjectRepository $customObjectRepository)
    {
        $this->customObjectRepository = $customObjectRepository;
    }

    public function transform($customObject)
    {
        if(!$customObject instanceof CustomObject) {
            throw new TransformationFailedException(sprintf("Object to transform must be an instance of %s", CustomObject::class));
        }

        return (new CustomObject_Dto())
            ->setId($customObject->getId())
            ->setLabel($customObject->getLabel())
            ->setInternalName($customObject->getInternalName());
    }

    public function reverseTransform($dto)
    {
        if(!$dto instanceof CustomObject_Dto) {
            throw new TransformationFailedException(sprintf("Dto must be an instance of %s", CustomObject_Dto::class));
        }

        if($dto->getId()) {
            $customObject = $this->customObjectRepository->find($dto->getId());
            if(!$customObject) {
                throw new TransformationFailedException('Custom object with dto id not found');
            }
        } else {
            $customObject = new CustomObject();
        }

        $customObject->setLabel($dto->getLabel())
            ->setInternalName($dto->getInternalName());

        return $customObject;
    }
}