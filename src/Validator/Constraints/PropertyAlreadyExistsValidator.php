<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PropertyAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class PropertyAlreadyExistsValidator extends ConstraintValidator
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

        $internalName = $protocol->getInternalName();
        $label = $protocol->getLabel();

        if($internalName && $this->propertyRepository->findByInternalName($internalName)) {
            $this->context->buildViolation($constraint->internalNameAlreadyExistsMessage)
                ->setParameter('{{ string }}', $internalName)
                ->atPath('internalName')
                ->addViolation();
        }

        if($label && $this->propertyRepository->findByLabel($label)) {
            $this->context->buildViolation($constraint->labelAlreadyExistsMessage)
                ->setParameter('{{ string }}', $label)
                ->atPath('label')
                ->addViolation();
        }
    }
}