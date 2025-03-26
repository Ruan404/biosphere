<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Route
{
    public function __construct(string $method, string $path)
    {
        $this->path = $path;
        $this->method = $method;
    }

    public string $path;
    

    public string $method;
    

}