<?php
require '../vendor/autoload.php';

use App\Auth\AuthController;
use App\Chat\ChatController;
use App\Film\FilmController;
use App\Core\Router;
use App\Home\HomeController;
use App\Podcast\PodcastController;
use App\Admin\AdminController;
use App\Sensor\SensorController;
use App\Message\MessageController;
use App\VideoStream\VideoStreamController;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

define('DEBUG_TIME', microtime(true));

// Redirect if URL has a trailing slash (but is not just "/")
if ($_SERVER['REQUEST_URI'] !== '/' && str_ends_with($_SERVER['REQUEST_URI'], '/')) {
        // Keep query string intact
        $uri = rtrim($_SERVER['REQUEST_URI'], '/');
        if (!empty($_SERVER['QUERY_STRING'])) {
                $uri .= '?' . $_SERVER['QUERY_STRING'];
        }

        header("Location: $uri", true, 301);
        exit;
}


$router = new Router();

if (session_status() == 1) {
        session_start();
}

$router->registerController(AdminController::class)
        ->registerController(HomeController::class)
        ->registerController(AuthController::class)
        ->registerController(ChatController::class)
        ->registerController(FilmController::class)
        ->registerController(PodcastController::class)
        ->registerController(SensorController::class)
        ->registerController(VideoStreamController::class)
        ->registerController(MessageController::class)
        ->run();