<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class CustomObjectDeletion extends Constraint
{
    public $customObjectHasPropertyGroups = 'Woahhh snap! The custom object "{{ string }}" has property groups assigned to it and can\'t be deleted until these are removed!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}