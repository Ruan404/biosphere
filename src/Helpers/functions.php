<?php
namespace App\Helpers;

use App\Entities\Layout;

function view($view, Layout $layout = Layout::Preset, array $data = []){
    $viewPath = dirname(__DIR__) . '../../templates';
    
    ob_start();
    require  $viewPath. DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view.'.php';
    $content = ob_get_clean();
    
    require $viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR .$layout->value. '.php';
}