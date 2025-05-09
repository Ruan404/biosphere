<?php
require '../vendor/autoload.php';

use App\Auth\AuthController;
use App\Chat\ChatController;
use App\Film\FilmController;
use App\Core\Router;
use App\Home\HomeController;
use App\Middleware\CsrfMiddleware;
use App\Middleware\IsLoggedInMiddleware;
use App\Middleware\RemoveTrailingSlashMiddleware;
use App\Podcast\PodcastController;
use App\Admin\AdminController;
use App\Sensor\SensorController;
Use App\Message\MessageController;
use App\VideoStream\VideoStreamController;
use GuzzleHttp\Psr7\ServerRequest;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

define('DEBUG_TIME', microtime(true));

$request = ServerRequest::fromGlobals();

$app = new Router();

$isLoggedIn = new IsLoggedInMiddleware();

$app->registerController(HomeController::class, [$isLoggedIn]);
$app->registerController(AuthController::class);
$app->registerController(ChatController::class, [$isLoggedIn]);
$app->registerController(FilmController::class, [$isLoggedIn]);
$app->registerController(PodcastController::class, [$isLoggedIn]);
$app->registerController(AdminController::class, [$isLoggedIn]);
$app->registerController(SensorController::class, [$isLoggedIn]);
$app->registerController(VideoStreamController::class, [$isLoggedIn]);
$app->registerController(MessageController::class, [$isLoggedIn]);

$handler = $app->middleware(
    [new RemoveTrailingSlashMiddleware()],
    $app
);

$response = $handler->handle($request);

http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value", false);
    }
}
echo $response->getBody();