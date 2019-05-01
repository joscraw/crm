<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class SystemDefined extends Constraint
{
    public $message = 'This is a system defined object and cannot be modified!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}