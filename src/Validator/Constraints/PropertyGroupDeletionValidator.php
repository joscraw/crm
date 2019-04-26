<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyGroupRepository;
use App\Repository\PropertyRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PropertyGroupDeletionValidator
 * @package App\Validator\Constraints
 */
class PropertyGroupDeletionValidator extends ConstraintValidator
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

        // Check to make sure the property group doesn't have any properties
        $results = $this->propertyRepository->getCountByPropertyGroup($protocol);

        $count = $results[0]['count'];

        if($count > 0) {
            $this->context->buildViolation($constraint->propertyGroupHasPropertiesMessage)
                ->setParameter('{{ string }}', $protocol->getName())
                ->addViolation();

            return;
        }

    }
}