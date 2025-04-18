<?php
namespace App\Exceptions;

use Exception;

abstract class HttpException extends Exception implements HttpExceptionInterface {
    protected int $statusCode = 500;
    protected string $publicMessage = 'An error occurred.';

    public function __construct(string $publicMessage = '', int $code = 0) {
        parent::__construct($publicMessage, $code);
        if ($publicMessage) {
            $this->publicMessage = $publicMessage;
        }
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }

    public function getPublicMessage(): string {
        return $this->publicMessage;
    }
}
