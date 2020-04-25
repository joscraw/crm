<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class ApiController
 * @package App\Controller\Api
 *
 */
class ApiController extends AbstractController
{
    public function getErrorsFromValidator(ConstraintViolationListInterface $validationErrors) {
        $errors = [];
        foreach ($validationErrors as $validationError) {
            $errors[$validationError->getPropertyPath()][] = $validationError->getMessage();
        }
        return $errors;
    }
}