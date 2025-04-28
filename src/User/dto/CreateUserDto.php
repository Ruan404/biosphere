<?php

namespace App\User\Dto;

Class CreateUserDto{

    public function __construct(public string $pseudo, public string $mdp){

    }

}