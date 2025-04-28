<?php

namespace App\User\Dto;

Class LoginUserDto{

    public function __construct(public string $pseudo, public string $mdp){

    }
}