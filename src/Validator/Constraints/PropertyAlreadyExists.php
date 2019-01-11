<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PropertyAlreadyExists extends Constraint
{
    public $internalNameAlreadyExistsMessage = 'The internal name "{{ string }}" is already in use for the custom object type "{{ string2 }}"!';
    public $labelAlreadyExistsMessage = 'The label "{{ string }}" is already in use for the custom object type "{{ string2 }}"';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}