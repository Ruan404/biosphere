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

function json($data, int $statusCode = 200): void
{
    ob_start();
    $data;
    ob_clean();
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function image_resize($file_name, $width, $height, $crop = FALSE) {
    list($wid, $ht) = getimagesize($file_name);
    $r = $wid / $ht;
    if ($crop) {
        if ($wid > $ht) {
            $wid = ceil($wid - ($width * abs($r - $width / $height)));
        } else {
            $ht = ceil($ht - ($ht * abs($r - $width / $height)));
        }
        $new_width = $width;
        $new_height = $height;
    } else {
        if ($width / $height > $r) {
            $new_width = $height * $r;
            $new_height = $height;
        } else {
            $new_height = $width / $r;
            $new_width = $width;
        }
    }
    $source = imagecreatefromjpeg($file_name);
    $dst = imagecreatetruecolor($new_width, $new_height);
    imagecopyresampled($dst, $source, 0, 0, 0, 0, $new_width, $new_height, $wid, $ht);
    return $dst;
}

function trimEscapeStrip(string $input){
    return nl2br(rtrim(strip_tags(htmlspecialchars_decode(trim($input)))));
}