<?php

namespace App\Http;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse extends JsonResponse
{

    /**
     * ApiResponse constructor.
     *
     * @param string $message
     * @param mixed  $data
     * @param array  $errors
     * @param int    $status
     * @param array  $headers
     * @param bool   $json
     */
    public function __construct(string $message = null, $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        if(!$message) {
            $message = isset(Response::$statusTexts[$status])
                ? Response::$statusTexts[$status]
                : 'Unknown status code';
        }

        parent::__construct($this->format($message, $data), $status, $headers, $json);
    }
    /**
     * Format the API response.
     *
     * @param string $message
     * @param mixed  $data
     *
     * @return array
     */
    private function format(string $message, $data = null)
    {
        if ($data === null) {
            $data = new \ArrayObject();
        }

        $response = [
            'message' => $message,
            'data'    => $data,
        ];

        return $response;
    }
}