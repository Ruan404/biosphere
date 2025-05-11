<?php

namespace App\Attributes;

use Attribute;
use Psr\Http\Server\MiddlewareInterface;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Middleware
{
    /**
     * @param \Psr\Http\Server\MiddlewareInterface[] $middlewares
     */
    public function __construct(MiddlewareInterface ...$middlewares) {
        $this->middlewares = $middlewares;
    }
}
