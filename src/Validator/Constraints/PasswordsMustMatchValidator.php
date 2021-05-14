<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class PasswordsMustMatchValidator
 * @package App\Validator\Constraints
 */
class PasswordsMustMatchValidator extends ConstraintValidator
{
    /**
     * @param mixed $protocol
     * @param Constraint $constraint
     */
    public function validate($protocol, Constraint $constraint)
    {

        if (!empty($protocol->getPassword())) {

            if ($protocol->getPassword() !== $protocol->getPasswordRepeat()) {

                $this->context->buildViolation($constraint->message)
                    ->atPath('password')
                    ->addViolation();


                $this->context->buildViolation($constraint->message)
                    ->atPath('passwordRepeat')
                    ->addViolation();

            }
        }
    }
}