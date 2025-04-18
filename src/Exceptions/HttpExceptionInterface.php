<?php

namespace App\Exceptions;

interface HttpExceptionInterface {
    public function getStatusCode(): int;
    public function getPublicMessage(): string;
}
