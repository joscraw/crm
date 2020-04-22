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
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @param bool $json
     */
    public function __construct(string $message = null, $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        if(!$message) {
            $message = isset(Response::$statusTexts[$status])
                ? Response::$statusTexts[$status]
                : 'Unknown status code';
        }

        parent::__construct($this->format($message, $data, $json), $status, $headers, $json);
    }

    /**
     * Format the API response.
     *
     * @param string $message
     * @param $data
     * @param $json
     *
     * @return array
     */
    private function format(string $message, $data, $json)
    {
        if ($data === null) {
            $data = new \ArrayObject();
            $data = json_encode($data);
        }

        $response = [
            'message' => $message,
            'data' => $data
        ];

        if($json === true) {
            $response['data'] = json_decode($data) === null ? $data : json_decode($data);
            return json_encode($response, true);
        }

        return $response;
    }
}