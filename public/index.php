<?php
require '../vendor/autoload.php';

use App\Auth\AuthController;
use App\Chat\ChatController;
use App\Core\Dispatcher;
use App\Film\FilmController;
use App\Core\Router;
use App\Home\HomeController;
use App\Middleware\IsLoggedInMiddleware;
use App\Middleware\RemoveTrailingSlashMiddleware;
use App\Podcast\PodcastController;
use App\Admin\AdminController;
use App\Sensor\SensorController;
Use App\Message\MessageController;
use App\VideoStream\VideoStreamController;
use GuzzleHttp\Psr7\ServerRequest;
use function App\Helpers\send;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

define('DEBUG_TIME', microtime(true));

$request = ServerRequest::fromGlobals();

$app = new Router();

$dispatcher = new Dispatcher();

$isLoggedIn = new IsLoggedInMiddleware();

$app->register(HomeController::class)
    ->register(AuthController::class)
    ->register(ChatController::class)
    ->register(FilmController::class)
    ->register(PodcastController::class)
    ->register(AdminController::class)
    ->register(SensorController::class)
    ->register(VideoStreamController::class)
    ->register(MessageController::class);

$dispatcher->pipe(new RemoveTrailingSlashMiddleware());
$dispatcher->pipe($app);
$response = $dispatcher->handle($request);

send($response);