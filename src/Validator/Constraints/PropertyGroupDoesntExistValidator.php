<?php

namespace App\Validator\Constraints;

use App\Repository\CustomObjectRepository;
use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use App\Repository\RoleRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PropertyGroupDoesntExistValidator
 * @package App\Validator\Constraints
 */
class PropertyGroupDoesntExistValidator extends ConstraintValidator
{
    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * PropertyGroupDoesntExistValidator constructor.
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
/*
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ string }}', 'hi')
            ->atPath('field.options')
            ->addViolation();*/


      /*  $customObject = $protocol->getCustomObject();
        $propertyGroups = $this->propertyGroupRepository->findBy(['customObject' => $customObject->getId()]);

        if(count($propertyGroups) === 0) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $customObject->getLabel())
                ->atPath('propertyGroup')
                ->addViolation();
        }*/
    }
}