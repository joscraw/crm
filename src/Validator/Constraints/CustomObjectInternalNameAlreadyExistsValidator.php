<?php

namespace App\Validator\Constraints;

use App\Repository\CustomObjectRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PropertyInternalNameAlreadyExistsValidator
 * @package App\Validator\Constraints
 */
class CustomObjectInternalNameAlreadyExistsValidator extends ConstraintValidator
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
        $internalName = $protocol->getInternalName();
        $portal = $protocol->getPortal();
        $customObjects = $this->customObjectRepository->findByInternalNameAndPortal($internalName, $portal);

        foreach ($customObjects as $customObject) {
            if ($customObject->getId() !== $protocol->getId()) {
                $this->context->buildViolation($constraint->internalNameAlreadyExistsMessage)
                    ->setParameter('{{ string }}', $internalName)
                    ->setParameter('{{ string2 }}', $protocol->getLabel())
                    ->atPath('internalName')
                    ->addViolation();
            }
        }
    }
}