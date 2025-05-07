<?php

namespace App\Helpers;

class Response
{
    public function json( $data, int $statusCode = 200): void
    {
        ob_start();
        $data;
        ob_clean();
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}