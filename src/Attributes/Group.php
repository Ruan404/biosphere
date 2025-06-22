<?php
namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Group
{
    public function __construct(public string $prefix) {}
}
