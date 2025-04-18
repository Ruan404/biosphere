<?php

namespace App\Exceptions;

Class NotFoundException extends HttpException{
    protected int $statusCode = 404;

    public function __construct(string $publicMessage = 'Resource not found') {
        parent::__construct($publicMessage, 404);
    }
}