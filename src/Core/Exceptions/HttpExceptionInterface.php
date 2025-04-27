<?php

namespace App\Core\Exceptions;

interface HttpExceptionInterface {
    public function getStatusCode(): int;
    public function getPublicMessage(): string;
}
