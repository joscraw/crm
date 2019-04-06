<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class FilterNameAlreadyExists extends Constraint
{
    public $message = 'The filter name "{{ string }}" is already in use!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}