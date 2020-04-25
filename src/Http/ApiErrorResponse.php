<?php

namespace App\Http;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

class ApiErrorResponse extends JsonResponse
{
    /**
     * @var mixed|string|null
     */
    public $message;

    /**
     * @var string|null
     */
    private $code;

    /**
     * @var array
     */
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
     * @param string|null $code
     * @param array $errors Almost exclusively used for form/entity validation errors as these come back as an array
     * @param int $status
     * @param array $headers
     * @param bool $json
     */
    public function __construct(string $message = null, string $code = null, $errors = [], int $status = 200, array $headers = [], bool $json = false)
    {
        if(!$message) {
            if ($code === null) {
                $message = isset(Response::$statusTexts[$status])
                    ? Response::$statusTexts[$status]
                    : 'Unknown status code';
            } else {
                if (!isset(self::$messages[$code])) {
                    throw new \InvalidArgumentException('No message for error code '.$code);
                }
                $message = self::$messages[$code];
            }
        }

        $this->message = $message;
        $this->code = $code;
        $this->errors = $errors;

        parent::__construct($this->format($message, $code, $errors), $status, $headers, $json);
    }

    /**
     * Format the API response.
     *
     * @param string $message
     * @param null $code
     * @param array $errors
     *
     * @return array
     */
    private function format(string $message, $code = null, $errors = [])
    {
        $response = [
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        if ($code) {
            $response['code'] = $code;
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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}