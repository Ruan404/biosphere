<?php
namespace App\Core;

use App\Attributes\Route;
use App\Attributes\Middleware;
use App\Attributes\Group;
use App\Entities\Layout;
use Dotenv\Dotenv;
use Exception;
use League\Route\RouteCollectionInterface;
use League\Route\Router as LeagueRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionMethod;
use function App\Helpers\view;

class Router extends LeagueRouter implements MiddlewareInterface
{
    public function __construct()
    {
        parent::__construct();

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    public function register(object|string $controller): self
    {
        $controllerInstance = is_string($controller) ? new $controller() : $controller;
        $reflection = new ReflectionClass($controllerInstance);

        $groupAttr = $reflection->getAttributes(Group::class)[0] ?? null;
        $middlewareAttr = $reflection->getAttributes(Middleware::class);

        $routePrefix = $groupAttr?->newInstance()->prefix ?? null;
        $classMiddlewares = $middlewareAttr ? $middlewareAttr[0]->newInstance()->middlewares : [];

        $registerMethods = function (RouteCollectionInterface $router) use ($controllerInstance, $reflection) {
            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $routeAttr = $method->getAttributes(Route::class)[0] ?? null;
                if (!$routeAttr) {
                    continue;
                }

                $route = $routeAttr->newInstance();
                $methodMiddlewareAttr = $method->getAttributes(Middleware::class);
                $methodMiddlewares = $methodMiddlewareAttr ? $methodMiddlewareAttr[0]->newInstance()->middlewares : [];

                $handler = [$controllerInstance, $method->getName()];
                $routeDef = $router->map($route->method, $route->path, $handler);

                if (!empty($methodMiddlewares)) {
                    $routeDef->middlewares($methodMiddlewares);
                }
            }
        };

        if ($routePrefix) {
            // Use group with prefix and apply class middlewares if present
            $group = $this->group($routePrefix, $registerMethods);
            if (!empty($classMiddlewares)) {
                $group->middlewares($classMiddlewares);
            }
        } else {
            // Use empty prefix group to apply class middleware
            if (!empty($classMiddlewares)) {
                $this->group('', function (RouteCollectionInterface $router) use ($registerMethods) {
                    $registerMethods($router);
                })->middlewares($classMiddlewares);
            } else {
                $registerMethods($this);
            }
        }

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Exception $e) {
            error_log("load route failed: " . $e->getMessage());
            return view('/errors/404', Layout::Error);
        }
    }
}
