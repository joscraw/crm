<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PasswordsMustMatch extends Constraint
{
    public $message = 'The passwords must match!';
    public $passwordRepeatMessage = 'You must enter a password repeat!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}