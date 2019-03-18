<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RoleNameAlreadyExists extends Constraint
{
    public $message = 'The role name "{{ string }}" is already in use!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}