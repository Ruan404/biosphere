<?php
require '../vendor/autoload.php';

use App\Auth\AuthController;
use App\Chat\ChatController;
use App\Core\Dispatcher;
use App\Film\FilmController;
use App\Core\Router;
use App\Home\HomeController;
use App\Middleware\RemoveTrailingSlashMiddleware;
use App\Podcast\PodcastController;
use App\Admin\AdminController;
use App\Sensor\SensorController;
use App\Message\MessageController;
use App\VideoStream\VideoStreamController;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use function App\Helpers\send;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;


$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
$whoops->register();

define('DEBUG_TIME', microtime(true));

$request = ServerRequest::fromGlobals();

if (session_status() === 1) {
        session_start();
}


$app = new Router();

$guard = new Guard(new ResponseFactory());
$guard->setFailureHandler(function (ServerRequestInterface $request) {
        $response = new Response();
        $response->getBody()->write("Not authorized");
        return $response->withStatus(403);
});

$dispatcher = new Dispatcher();


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
// $dispatcher->pipe($guard);
$dispatcher->pipe($app);
$response = $dispatcher->handle($request);

send($response);
