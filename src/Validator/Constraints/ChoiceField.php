<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class ChoiceField extends Constraint
{
    public $duplicateOptionMessage = 'Woahhh snap! You can\'t have more than one option with the same name!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}