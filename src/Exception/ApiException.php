<?php

namespace App\Controller\Exception;

use App\Http\ApiErrorResponse;
use \Exception as Exception;

abstract class ApiException extends Exception
{
    private $errorNumber;

    public function __construct(ApiErrorResponse $apiErrorResponse)
    {
        parent::__construct($msg, $statusCode);

        $this->errorNumber = $errorNumber;
    }

    /**
     * @return Exception
     */
    public function getErrorNumber()
    {
        return $this->errorNumber;
    }
}