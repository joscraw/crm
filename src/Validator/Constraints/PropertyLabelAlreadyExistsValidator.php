<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PropertyLabelAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class PropertyLabelAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @var PropertyRepository
     */
    private $propertyRepository;

    /**
     * @PropertyAlreadyExists constructor.
     * @param PropertyRepository $propertyRepository
     */
    public function __construct(PropertyRepository $propertyRepository) {
        $this->propertyRepository = $propertyRepository;
    }

    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {
        $label = $protocol->getLabel();
        $customObject = $protocol->getCustomObject();
        $properties = $this->propertyRepository->findByLabelAndCustomObject($label, $customObject);

        foreach($properties as $property) {
            if($property->getId() !== $protocol->getId()) {
                $this->context->buildViolation($constraint->labelAlreadyExistsMessage)
                    ->setParameter('{{ string }}', $label)
                    ->setParameter('{{ string2 }}', $protocol->getCustomObject()->getLabel())
                    ->atPath('label')
                    ->addViolation();

                return;
            }
        }
    }
}