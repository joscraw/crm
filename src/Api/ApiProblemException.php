<?php

namespace App\Api;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiProblemException extends HttpException
{
    public function __construct(int $statusCode, string $message = null, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}