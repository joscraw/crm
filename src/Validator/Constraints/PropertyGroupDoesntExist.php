<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PropertyGroupDoesntExist extends Constraint
{
    public $message = 'No property groups exist for "{{ string }}". Make sure to create one first!';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}