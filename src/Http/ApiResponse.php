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
     * @param null $extraData
     * @param int $status
     * @param array $headers
     * @param bool $json
     */
    public function __construct(string $message = null, $data = null, $extraData = null, int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct($this->format($message, $data, $extraData, $json), $status, $headers, $json);
    }

    /**
     * Format the API response.
     *
     * @param $message
     * @param $data
     * @param $extraData
     * @param $json
     *
     * @return array
     */
    private function format($message, $data, $extraData, $json)
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

        if($extraData) {
            $response = array_merge($extraData, $response);
        }

        if($json === true) {
            $response['data'] = json_decode($data) === null ? $data : json_decode($data);
            return json_encode($response, true);
        }

        return $response;
    }
}