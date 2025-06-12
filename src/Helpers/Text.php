<?php

namespace App\Helpers;
class Text
{

    public static function getFirstStr(string $text): string
    {

        return mb_substr($text, 0, 1);
    }

    public static function escapeAndRemoveUnderscore(string $text): string
    {
        //&nbsp; is non breaking space html special character
        return str_replace("_", "&nbsp;", htmlspecialchars($text));
    }

}