<?php
namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;

class Csrf
{
    public static function get(ServerRequestInterface $request)
    {
        $nameKey = $request->getAttribute('csrf_name');
        $valueKey = $request->getAttribute('csrf_value');

        return [
            'csrf_name' => $nameKey,
            'csrf_value' => $valueKey
        ];
    }
}