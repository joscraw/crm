<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class PortalNotFoundForInternalIdentifier extends Constraint
{
    public $message = 'Portal not found for internal identifier.';

}