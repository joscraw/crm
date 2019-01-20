<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PropertyLabelAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class CustomObjectLabelAlreadyExistsValidator extends ConstraintValidator
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($protocol, Constraint $constraint)
    {
        $label = $protocol->getLabel();

        if($label && $this->propertyRepository->findByLabel($label)) {
            $this->context->buildViolation($constraint->labelAlreadyExistsMessage)
                ->setParameter('{{ string }}', $label)
                ->setParameter('{{ string2 }}', $protocol->getCustomObject()->getLabel())
                ->atPath('label')
                ->addViolation();
        }
    }
}