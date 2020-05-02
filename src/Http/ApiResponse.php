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
        parent::__construct($status !== Response::HTTP_NO_CONTENT ? $this->format($message, $data, $json) : null, $status, $headers, $json);
    }

    /**
     * Format the API response.
     *
     * @param $message
     * @param $data
     * @param $json
     *
     * @return array
     */
    private function format($message, $data, $json)
    {
        if ($data === null) {
            $data = new \ArrayObject();
            $data = json_encode($data);
        }

        $response = [
            'data' => $data
        ];

        if($message) {
            $response['message'] = $message;
        }

        if($json === true) {
            $response['data'] = json_decode($data, true) === null ? $data : json_decode($data, true);
            return json_encode($response, true);
        }

        return $response;
    }
}