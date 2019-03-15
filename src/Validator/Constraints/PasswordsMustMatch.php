<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PasswordsMustMatch extends Constraint
{
    public $message = 'The passwords must match!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}