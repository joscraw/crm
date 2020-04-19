<?php

namespace App\Http;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiErrorResponse extends JsonResponse
{
    private $message;
    private $errorCode;
    private $errors;

    const TYPE_VALIDATION_ERROR = 'validation_error';
    const TYPE_INVALID_REQUEST_BODY_FORMAT = 'invalid_body_format';
    const TYPE_QUERY_ERROR = 'query_error';

    /**
     * Error Code to Message Map.
     *
     * @var array
     */
    private static $messages = array(
        self::TYPE_VALIDATION_ERROR => 'There was a validation error',
        self::TYPE_INVALID_REQUEST_BODY_FORMAT => 'Invalid JSON format sent',
        self::TYPE_QUERY_ERROR => 'There was a query error'
    );

    /**
     * ApiResponse constructor.
     *
     * @param string|null $message A basic message for the error
     * @param string $errorCode A short string that maps to the $messages defined at the top of this class
     * @param array $errors Almost exclusively used for form/entity validation errors as these come back as an array
     * @param int $status
     * @param array $headers
     * @param bool $json
     */
    public function __construct(string $message = null, string $errorCode = null, array $errors = [], int $status = 200, array $headers = [], bool $json = false)
    {
        if(!$message) {
            if ($errorCode === null) {
                $message = isset(Response::$statusTexts[$status])
                    ? Response::$statusTexts[$status]
                    : 'Unknown status code';
            } else {
                if (!isset(self::$messages[$errorCode])) {
                    throw new \InvalidArgumentException('No message for error code '.$errorCode);
                }
                $message = self::$messages[$errorCode];
            }
        }

        $this->message = $message;
        $this->errorCode = $errorCode;
        $this->errors = $errors;

        parent::__construct($this->format($message, $errorCode, $errors), $status, $headers, $json);
    }

    /**
     * Format the API response.
     *
     * @param string $message
     * @param null $errorCode
     * @param array $errors
     *
     * @return array
     */
    private function format(string $message, $errorCode = null, array $errors = [])
    {
        $response = [
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($errorCode) {
            $response['code'] = $errorCode;
        }

        return $response;
    }

    /**
     * @return mixed|string|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}