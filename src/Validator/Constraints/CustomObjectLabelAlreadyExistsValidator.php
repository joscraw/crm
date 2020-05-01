<?php

namespace App\Validator\Constraints;

use App\Repository\CustomObjectRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PropertyLabelAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class CustomObjectLabelAlreadyExistsValidator extends ConstraintValidator
{
    /**
     * @var CustomObjectRepository
     */
    private $customObjectRepository;

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
        return;
        $label = $protocol->getLabel();
        $portal = $protocol->getPortal();
        $customObject = $this->customObjectRepository->findByLabelAndPortal($label, $portal);

        if(!$customObject) {
            return;
        }

        if ($customObject->getId() === $protocol->getId()) {
            return;
        }

        $this->context->buildViolation($constraint->labelAlreadyExistsMessage)
            ->setParameter('{{ string }}', $label)
            ->setParameter('{{ string2 }}', $protocol->getLabel())
            ->atPath('label')
            ->addViolation();

    }
}