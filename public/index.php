<?php
require '../vendor/autoload.php'; 

use App\Auth\AuthController;
use App\Chat\ChatController;
use App\Film\FilmController;
use App\Core\Router;
use App\Home\HomeController;
use App\Message\MessageController;
use App\Podcast\PodcastController;
use App\Admin\AdminController;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

define('DEBUG_TIME', microtime(true));

$router = new Router();

$router->registerController(HomeController::class);
$router->registerController(AuthController::class);
$router->registerController(ChatController::class);
$router->registerController(FilmController::class);
$router->registerController(PodcastController::class);
$router->registerController(MessageController::class);
$router->registerController(AdminController::class);

$router->run();