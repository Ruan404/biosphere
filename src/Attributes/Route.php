<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public function __construct(string $method, string $path)
    {
        $this->path = $path;
        $this->method = $method;
    }

    public string $path{
        get => $this->path;
    }

    public string $method{
        get => $this->method;
    }

}