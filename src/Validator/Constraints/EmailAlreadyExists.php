<?php


namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class EmailAlreadyExists extends Constraint
{
    public $message = 'Email is already in use by another user.';

}