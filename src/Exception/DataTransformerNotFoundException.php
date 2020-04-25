<?php

namespace App\Exception;

class DataTransformerNotFoundException extends \Exception
{
    public function __construct($msg, $code = 0, \Exception $previous = null)
    {
        parent::__construct($msg, $code, $previous);
    }
}