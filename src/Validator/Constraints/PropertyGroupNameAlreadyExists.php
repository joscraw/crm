<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PropertyGroupNameAlreadyExists extends Constraint
{
    public $nameAlreadyExistsMessage = 'The name "{{ string }}" is already being used by another property group!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}