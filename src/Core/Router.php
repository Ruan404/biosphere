<?php
namespace App\Core;

use AltoRouter;
use App\Attributes\Route;
use App\Attributes\Middleware;
use App\Middleware\ControllerMiddleware;
use Dotenv\Dotenv;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Router implements MiddlewareInterface
{
    private AltoRouter $router;
    private FilesystemAdapter $cache;
    private bool $routesCached = false;

    private $cacheItem;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');

        $dotenv->load();
        $cacheDir = $_ENV["CACHE_DIR"];
        $routesCacheKey = $_ENV["ROUTES_CACHE_KEY"];
        $routesCacheDuration = $_ENV["ROUTES_CACHE_DURATION"];

        $this->router = new AltoRouter();

        $this->cache = new FilesystemAdapter(defaultLifetime: $routesCacheDuration , directory: __DIR__ . '/../../' . $cacheDir);
        $this->cacheItem = $this->cache->getItem($routesCacheKey);
        $this->routesCached = $this->cacheItem->isHit();
    }

    public function register(object|string $controller): self
    {
        // Don’t allow dynamic registration once cache is loaded
        if ($this->routesCached) {
            return $this;
        }

        $controllerInstance = is_string($controller) ? new $controller() : $controller;
        $reflection = new ReflectionClass($controllerInstance);

        $routePrefixAttr = $reflection->getAttributes(Route::class);
        $middlewareAttr = $reflection->getAttributes(Middleware::class);

        $routePrefix = '';
        if (!empty($routePrefixAttr)) {
            $routePrefix = $routePrefixAttr[0]->newInstance()->path;
        }

        $classMiddlewares = [];
        if (!empty($middlewareAttr)) {
            $classMiddlewares = $middlewareAttr[0]->newInstance()->middlewares;
        }

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttr = $method->getAttributes(Route::class)[0] ?? null;
            if (!$routeAttr)
                continue;

            $route = $routeAttr->newInstance();

            $methodMiddlewares = [];
            $methodMiddlewareAttr = $method->getAttributes(Middleware::class);
            if (!empty($methodMiddlewareAttr)) {
                $methodMiddlewares = $methodMiddlewareAttr[0]->newInstance()->middlewares;
            }

            // Final controller as middleware
            $controllerMiddleware = new ControllerMiddleware($controllerInstance, $method->getName());


            $middlewares = array_merge($classMiddlewares, $methodMiddlewares, [$controllerMiddleware]);

            $dispatcher = new Dispatcher();
            foreach ($middlewares as $middleware) {
                $dispatcher->pipe($middleware);
            }

            $this->router->map($route->method, $routePrefix . $route->path, $dispatcher);
        }

        return $this;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->cacheItem->isHit()) {
            $routes = $this->cacheItem->get();
            $this->router->addRoutes($routes);

        } else {
            $this->cacheItem->set($this->router->getRoutes());

            $this->cache->save($this->cacheItem);
        }

        $match = $this->router->match();
        $target = $match['target'] ?? null;

        if (!empty($match['params'])) {
            $request = $request->withAttribute('params', $match['params']);
        }

        return $target->handle($request);
    }
}
