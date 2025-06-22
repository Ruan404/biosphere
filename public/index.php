<?php
require '../vendor/autoload.php';

use App\Auth\AuthController;
use App\Chat\ChatController;
use App\Core\Dispatcher;
use App\File\FileController;
use App\Film\FilmController;
use App\Core\Router;
use App\Home\HomeController;
use App\Middleware\AccessControlMiddleware;
use App\Middleware\RemoveTrailingSlashMiddleware;
use App\Podcast\PodcastController;
use App\Admin\AdminController;
use App\Profile\ProfileController;
use App\Sensor\SensorController;
use App\Message\MessageController;
use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\Route\Strategy\ApplicationStrategy;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

define('DEBUG_TIME', microtime(true));



if (session_status() === 1) {
        session_start();
}

$request = ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$dispatch = new Dispatcher();
$router = new Router();
$router->setStrategy(new ApplicationStrategy());

// $guard = new Guard(new ResponseFactory());
// $guard->setFailureHandler(function (ServerRequestInterface $request) {
//         $response = new Response();
//         $response->getBody()->write("Not authorized");
//         return $response->withStatus(403);
// });
$router->middleware(new AccessControlMiddleware);
$router->register(HomeController::class)
        ->register(AuthController::class)
        ->register(ChatController::class)
        ->register(FilmController::class)
        ->register(PodcastController::class)
        ->register(AdminController::class)
        ->register(SensorController::class)
        ->register(MessageController::class)
        ->register(FileController::class)
        ->register(ProfileController::class);


$dispatch->pipe($router);
$response = $dispatch->handle($request);


new SapiEmitter()->emit($response);
