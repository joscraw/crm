<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CustomObjectDeletion extends Constraint
{
    public $customObjectHasPropertyGroupsMessage = 'Woahhh snap! The custom object "{{ string }}" has property groups assigned to it and can\'t be deleted until these are removed!';
    public $systemDefinedObjectMessage = 'Woahhh snap! This is a system defined object and cannot be deleted!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}