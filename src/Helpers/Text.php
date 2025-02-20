<?php

namespace App\Helpers;
Class Text{

    public static function getFirstStr(string $text){

        return mb_substr($text,0,1);
    }

}