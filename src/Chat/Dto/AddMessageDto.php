<?php

namespace App\Chat\Dto;

class AddMessageDto
{
    public function __construct(public string $pseudo,public string $message,public string $topicName, public ?array $image = null){}

}