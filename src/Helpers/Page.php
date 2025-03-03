<?php 
namespace App\Helpers;

use App\Entities\Layout;

Class Page {
    public static function print($view, Layout $layout = Layout::Preset, array $infos = []){
        $viewPath = dirname(__DIR__) . '../../templates';

        $data = $infos;
        ob_start();
        require  $viewPath. DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view.'.php';
        $content = ob_get_clean();
        
        require $viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR .$layout->value. '.php';
    }
}