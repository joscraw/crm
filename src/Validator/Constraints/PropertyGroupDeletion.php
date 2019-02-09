<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PropertyGroupDeletion extends Constraint
{
    public $propertyGroupHasPropertiesMessage = 'Woahhh snap! The property group "{{ string }}" has properties assigned to it and can\'t be deleted until these are removed!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}