<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SystemDefinedValidator
 * @package App\Validator\Constraints
 */
class SystemDefinedValidator extends ConstraintValidator
{
    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {
        if($protocol->isSystemDefined()) {

            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }

    }
}