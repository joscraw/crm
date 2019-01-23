<?php

namespace App\Validator\Constraints;

use App\Repository\PropertyRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class PropertyInternalNameAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class PropertyInternalNameAlreadyExistsValidator extends ConstraintValidator
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
        $customObject = $protocol->getCustomObject();
        $properties = $this->propertyRepository->findByInternalNameAndCustomObject($internalName, $customObject);

        foreach($properties as $property) {
            if($property->getId() !== $protocol->getId()) {
                if(count($properties) > 0) {
                    $this->context->buildViolation($constraint->internalNameAlreadyExistsMessage)
                        ->setParameter('{{ string }}', $internalName)
                        ->setParameter('{{ string2 }}', $protocol->getCustomObject()->getLabel())
                        ->atPath('internalName')
                        ->addViolation();
                }

                return;
            }
        }
    }
}