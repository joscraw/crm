<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class InvalidInputException extends ApiException
{
    public function __construct($msg, $errorNumber)
    {
        parent::__construct($msg, Response::HTTP_BAD_REQUEST, $errorNumber);
    }
}