<?php
require '../vendor/autoload.php'; 

use App\Auth\AuthController;
use App\Core\Router;
use App\Home\HomeController;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

$router = new Router();

$router->registerController(HomeController::class);
$router->registerController(AuthController::class);


$router->run();