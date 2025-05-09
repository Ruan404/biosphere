<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Middleware
{
    /**
     * @param array<class-string> $classes
     */
    public function __construct(public array $classes) {}
}
