<?php

namespace App\Core;

use AltoRouter;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionMethod;
use App\Attributes\Route;
use App\Attributes\Middleware;

class Router implements RequestHandlerInterface
{
    private AltoRouter $router;

    public function __construct()
    {
        $this->router = new AltoRouter();

    }
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->run($request);
    }

    public function map(string $method, string $path, callable|RequestHandlerInterface $controller): void
    {
        $this->router->map($method, $path, $controller);
    }

    public function registerController($controller, array $controllerMiddlewares = []): void
    {
        $reflection = new ReflectionClass($controller);

        $routeAttributes = $reflection->getAttributes(Route::class);

        $prefix = '';

        if (!empty($routeAttributes)) {
            $prefix = $routeAttributes[0]->newInstance()->path;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttr = $method->getAttributes(Route::class)[0] ?? null;
            if (!$routeAttr)
                continue;

            $route = $routeAttr->newInstance();

            // method-level middleware
            $middlewareAttr = $method->getAttributes(Middleware::class)[0] ?? null;
            $methodMiddlewares = [];

            if ($middlewareAttr) {
                foreach ($middlewareAttr->newInstance()->classes as $class) {
                    $methodMiddlewares[] = new $class();
                }
            }

            // combined middleware stack
            $allMiddlewares = array_merge($controllerMiddlewares, $methodMiddlewares);

            $handler = fn(ServerRequestInterface $req) => $method->invoke(new $controller(), $req);

            $this->map(
                $route->method,
                $prefix . $route->path,
                $this->middleware($allMiddlewares, $handler)
            );
        }
    }

    public function middleware(array $middlewares, callable|RequestHandlerInterface $handler): RequestHandlerInterface
    {
        $finalHandler = $handler instanceof RequestHandlerInterface
            ? $handler
            : new class ($handler) implements RequestHandlerInterface {
            public function __construct(private $handler)
            {
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return ($this->handler)($request);
            }
            };

        return array_reduce(
            array_reverse($middlewares),
            fn(RequestHandlerInterface $next, MiddlewareInterface $middleware) =>
            new class ($middleware, $next) implements RequestHandlerInterface {
            public function __construct(
                    private MiddlewareInterface $middleware,
                    private RequestHandlerInterface $next
                ) {
                }

                public function handle(ServerRequestInterface $request): ResponseInterface
                {
                    return $this->middleware->process($request, $this->next);
                }
                },
            $finalHandler
        );
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $match = $this->router->match();
        $target = $match['target'] ?? null;

        if (!$target) {
            return new Response(404, ['Content-Type' => 'text/plain'], 'Not Found');
        }

        if (!empty($match['params'])) {
            $request = $request->withAttribute('params', $match['params']);
        }

        if ($target instanceof RequestHandlerInterface) {
            return $target->handle($request);
        }

        if (is_callable($target)) {
            return $target($request, new Response());
        }

        return new Response(500, ['Content-Type' => 'text/plain'], 'Invalid route handler');
    }
}
