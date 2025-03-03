<?php 
namespace App\Helpers;

use App\Entities\Layout;

Class Page {
    public static function print($view, Layout $layout = Layout::Preset){
        $viewPath = dirname(__DIR__) . '../../templates';

        ob_start();
        require  $viewPath. DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view.'.php';
        $content = ob_get_clean();
        require $viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR .$layout->value. '.php';
    }
}