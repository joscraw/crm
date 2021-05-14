<?php

namespace App\Controller\Exception;

use \Exception as Exception;

abstract class ApiException extends Exception
{
    private $errorNumber;

    public function __construct($msg, $statusCode, $errorNumber)
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