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
Use App\Message\MessageController;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

define('DEBUG_TIME', microtime(true));

$router = new Router();

$router ->registerController(HomeController::class)
        ->registerController(AuthController::class)
        ->registerController(ChatController::class)
        ->registerController(FilmController::class)
        ->registerController(PodcastController::class)
        ->registerController(AdminController::class)
        ->registerController(SensorController::class)
        ->registerController(MessageController::class)
        ->run();