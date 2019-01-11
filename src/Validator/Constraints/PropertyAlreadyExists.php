<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PropertyAlreadyExists extends Constraint
{
    public $internalNameAlreadyExistsMessage = 'The internal name "{{ string }}" is already in use!';
    public $labelAlreadyExistsMessage = 'The label "{{ string }}" is already in use!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}