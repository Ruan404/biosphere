<?php

namespace App\Core;

use AltoRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionMethod;
use App\Attributes\Route;
use App\Attributes\Middleware;

class Router implements MiddlewareInterface
{
    private AltoRouter $router;

    public function __construct()
    {
        $this->router = new AltoRouter();
    }

    public function register(object|string $controller): self
    {
        $controllerInstance = is_string($controller) ? new $controller() : $controller;
        $reflection = new ReflectionClass($controllerInstance);

        $routePrefixAttr = $reflection->getAttributes(Route::class);
        $middlewareAttr = $reflection->getAttributes(Middleware::class);
        $classMiddlewares = [];
        $methodMiddlewares = [];
        $routePrefix = '';
        $middlewares = [];

        if (!empty($routePrefixAttr)) {
            $routePrefix = $routePrefixAttr[0]->newInstance()->path;

        }

        if (!empty($classMiddlewares)) {
            $classMiddlewares = $middlewareAttr[0]->newInstance()->middlewares;
        }



        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttr = $method->getAttributes(Route::class)[0] ?? null;
            if (!$routeAttr)
                continue;

            $route = $routeAttr->newInstance();

            $methodMiddlewareAttr = $method->getAttributes(Middleware::class);

            if (!empty($methodMiddlewares)) {
                $methodMiddlewares = $methodMiddlewareAttr[0]->newInstance()->middlewares ?? [];
            }

            // Final middleware from controller method
            $controllerMiddleware = new class ($controllerInstance, $method) implements MiddlewareInterface {
                public function __construct(
                    private object $controller,
                    private ReflectionMethod $method
                ) {
                }

                public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
                {
                    return $this->method->invoke($this->controller, $request);
                }
            };

            $middlewares = array_merge($classMiddlewares, $methodMiddlewares, [$controllerMiddleware]);


            // Build dispatcher
            $dispatcher = new Dispatcher();

            foreach ($middlewares as $middleware) {
                $dispatcher->pipe($middleware);
            }
            // Register route with dispatcher
            $this->router->map($route->method, $routePrefix . $route->path, $dispatcher);
        }

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $match = $this->router->match();
        $target = $match['target'] ?? null;

        if (!empty($match['params'])) {
            $request = $request->withAttribute('params', $match['params']);
        }

        return $target->handle($request);
    }
}
