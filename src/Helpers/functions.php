<?php
namespace App\Helpers;

use App\Entities\Layout;


use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response;

function view(string $view, Layout $layout = Layout::Preset, array $data = []): ResponseInterface {
    $viewPath = dirname(__DIR__) . '../../templates';

    $templatePath =  $viewPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';;
    $layoutPath =  $viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout->value . '.php';

     ob_start();
     require $templatePath;
     $content = ob_get_clean();
 
     ob_start();
     require $layoutPath;
     $html = ob_get_clean();
   
    return new Response(200, ['Content-Type' => 'text/html'], $html);
}


function json(array $data, int $status = 200): ResponseInterface {
    return new Response(
        $status,
        ['Content-Type' => 'application/json'],
        json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    );
}


function generateRandomString($length = 10, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
