<?php

namespace App\Chat\Dto;

class CreateChatDto
{
    public function __construct(public string $pseudo,public string $message){}

}