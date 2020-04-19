<?php

namespace App\Exception;

use App\Http\ApiErrorResponse;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiException extends HttpException
{
    private $apiErrorResponse;

    public function __construct(ApiErrorResponse $apiErrorResponse, \Exception $previous = null, array $headers = array(), $code = 0)
    {
        $this->apiErrorResponse = $apiErrorResponse;

        parent::__construct($apiErrorResponse->getStatusCode(), $apiErrorResponse->getMessage(), $previous, $headers, $code);
    }

    /**
     * @return ApiErrorResponse
     */
    public function getApiErrorResponse(): ApiErrorResponse
    {
        return $this->apiErrorResponse;
    }
}