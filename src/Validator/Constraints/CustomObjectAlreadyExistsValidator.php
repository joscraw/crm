<?php

namespace App\Validator\Constraints;

use App\Repository\CustomObjectRepository;
use App\Repository\PropertyRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * Class CustomObjectAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class CustomObjectAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

    /**
     * CustomObjectAlreadyExistsValidator constructor.
     * @param CustomObjectRepository $customObjectRepository
     */
    public function __construct(CustomObjectRepository $customObjectRepository)
    {
        $this->customObjectRepository = $customObjectRepository;
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

        if($internalName && $this->customObjectRepository->findByInternalName($internalName)) {
            $this->context->buildViolation($constraint->internalNameAlreadyExistsMessage)
                ->setParameter('{{ string }}', $internalName)
                ->atPath('internalName')
                ->addViolation();
        }

        if($label && $this->customObjectRepository->findByLabel($label)) {
            $this->context->buildViolation($constraint->labelAlreadyExistsMessage)
                ->setParameter('{{ string }}', $label)
                ->atPath('label')
                ->addViolation();
        }
    }
}