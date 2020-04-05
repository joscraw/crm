<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyGroupRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
        // Check to make sure the custom object doesn't have any property groups
        $results = $this->propertyGroupRepository->getCountByCustomObject($protocol);

        $count = $results[0]['count'];

        if($count > 0) {
            $this->context->buildViolation($constraint->customObjectHasPropertyGroupsMessage)
                ->setParameter('{{ string }}', $protocol->getLabel())
                ->addViolation();

            return;
        }


    }
}