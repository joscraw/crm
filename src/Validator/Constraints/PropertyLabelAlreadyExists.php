<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PropertyLabelAlreadyExists extends Constraint
{
    public $labelAlreadyExistsMessage = 'The label "{{ string }}" is already in use for the custom object type "{{ string2 }}"!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}