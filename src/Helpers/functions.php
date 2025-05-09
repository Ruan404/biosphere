<?php
namespace App\Helpers;

use App\Entities\Layout;

function view($view, Layout $layout = Layout::Preset, array $data = [])
{
    $viewPath = dirname(__DIR__) . '../../templates';

    if ($layout !== Layout::Clean) {
        ob_start();
        require $viewPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
        $content = ob_get_clean();

        require $viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout->value . '.php';
        require $viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . 'footer.php';

    } else {
        require $viewPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
    }
}

function generateRandomString($length = 10, $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
