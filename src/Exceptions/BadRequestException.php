<?php

namespace App\Exceptions;

Class BadRequestException extends HttpException{
    protected int $statusCode = 400;

    public function __construct(string $publicMessage = 'Validation failed') {
        parent::__construct($publicMessage);
    }
}