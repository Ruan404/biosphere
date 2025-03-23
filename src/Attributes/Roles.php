<?php

namespace App\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Roles
{
    public function __construct(array $roles)
    {
        $this->roles = $roles;
    }

    public array $roles{
        get => $this->roles;
    }

}