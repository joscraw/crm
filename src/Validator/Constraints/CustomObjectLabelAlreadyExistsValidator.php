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
     */
    public function validate($protocol, Constraint $constraint)
    {
        $label = $protocol->getLabel();
        $portal = $protocol->getPortal();
        $customObjects = $this->customObjectRepository->findByLabelAndPortal($label, $portal);

        foreach ($customObjects as $customObject) {
            if ($customObject->getId() !== $protocol->getId()) {
                $this->context->buildViolation($constraint->labelAlreadyExistsMessage)
                    ->setParameter('{{ string }}', $label)
                    ->setParameter('{{ string2 }}', $protocol->getLabel())
                    ->atPath('label')
                    ->addViolation();
            }
        }
    }
}