<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class CustomObjectDeletionValidator
 * @package App\Validator\Constraints
 */
class CustomObjectDeletionValidator extends ConstraintValidator
{
    /**
     * @var PropertyGroupRepository
     */
    private $propertyGroupRepository;

    /**
     * @PropertyAlreadyExists constructor.
     * @param PropertyGroupRepository $propertyGroupRepository
     */
    public function __construct(PropertyGroupRepository $propertyGroupRepository) {
        $this->propertyGroupRepository = $propertyGroupRepository;
    }

    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function validate($protocol, Constraint $constraint)
    {
        $results = $this->propertyGroupRepository->getCountByCustomObject($protocol);

        $count = $results[0]['count'];

        if($count > 0) {
            $this->context->buildViolation($constraint->customObjectHasPropertyGroups)
                ->setParameter('{{ string }}', $protocol->getLabel())
                /*->atPath('submit')*/
                ->addViolation();
        }


    }
}