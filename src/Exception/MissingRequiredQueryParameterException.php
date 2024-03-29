<?php

namespace App\Controller\Exception;

use Symfony\Component\HttpFoundation\Response;

class MissingRequiredQueryParameterException extends ApiException
{
    public function __construct($msg, $errorNumber)
    {
        parent::__construct($msg, Response::HTTP_BAD_REQUEST, $errorNumber);
    }
}
