<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RecordProperty extends Constraint
{
    public $emailAlreadyExistsMessage = 'The email "{{ string }}" is already in use.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}