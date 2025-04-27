<?php

namespace App\Attributes;

use Attribute;
#[Attribute(Attribute::TARGET_CLASS)]
Class Middlewares{
    public function __construct(array $name){
        $this->name = $name;
    }

    public array $name{
        get => $this->name;
    }
}