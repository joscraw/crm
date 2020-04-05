<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyGroupRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PropertyGroupNameAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class PropertyGroupNameAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * PropertyGroupNameAlreadyExistsValidator constructor.
     * @param PropertyGroupRepository $propertyGroupRepository
     */
    public function __construct(PropertyGroupRepository $propertyGroupRepository)
    {
        $this->propertyGroupRepository = $propertyGroupRepository;
    }


    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {

        $name = $protocol->getName();
        $customObject = $protocol->getCustomObject();
        $propertyGroups = $this->propertyGroupRepository->findByNameAndCustomObject($name, $customObject);

        foreach($propertyGroups as $propertyGroup) {
            if($propertyGroup->getId() !== $protocol->getId()) {
                    $this->context->buildViolation($constraint->nameAlreadyExistsMessage)
                        ->setParameter('{{ string }}', $name)
                        ->setParameter('{{ string2 }}', $customObject->getLabel())
                        ->atPath('name')
                        ->addViolation();
                return;
            }
        }
    }
}