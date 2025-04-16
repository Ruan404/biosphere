<?php

namespace App\Helpers;

class Response
{
    public function json(array $data, int $statusCode = 200): void
    {
        ob_start();
        $response = $data;
        ob_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response);
        exit;
    }
}