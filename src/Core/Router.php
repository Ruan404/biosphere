<?php
namespace App\Core;

use App\Attributes\Route;
use App\Attributes\Middleware;
use App\Attributes\Group;
use App\Entities\Layout;
use Dotenv\Dotenv;
use Exception;
use GuzzleHttp\Psr7\Response;
use League\Route\Http\Exception\NotFoundException;
use League\Route\RouteCollectionInterface;
use League\Route\Router as LeagueRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use function App\Helpers\json;
use function App\Helpers\view;

class Router extends LeagueRouter
{
    private FilesystemAdapter $cache;
    private bool $routesCached = false;
    private $cacheItem;

    private $roads;

    public function __construct()
    {
        parent::__construct();

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        // Assuming environment is already loaded elsewhere in bootstrap:
        $cacheDir = $_ENV["CACHE_DIR"];
        $routesCacheKey = $_ENV["ROUTES_CACHE_KEY"];
        $routesCacheDuration = (int) ($_ENV["ROUTES_CACHE_DURATION"]);

        $this->cache = new FilesystemAdapter(
            defaultLifetime: $routesCacheDuration,
            directory: __DIR__ . '/../../' . $cacheDir
        );
        $this->cacheItem = $this->cache->getItem($routesCacheKey);
        $this->routesCached = $this->cacheItem->isHit();

        if ($this->routesCached) {
            $cachedRoutes = $this->cacheItem->get();
            foreach ($cachedRoutes as $route) {
                $handler = function (ServerRequestInterface $request, array $args) use ($route) {
                    $controller = new $route['handler']['controller']();
                    $method = $route['handler']['method'];
                    return $controller->$method($request, ...array_values($args));
                };
                $routeDef = $this->map($route['method'], $route['path'], $handler);

                // If you cached middlewares, apply them here as well
                if (!empty($route['middlewares'] ?? [])) {
                    $routeDef->middlewares($route['middlewares']);
                }
            }
        }
    }

    public function register(object|string $controller): self
    {
        if ($this->routesCached) {
            // Don't register if routes are cached
            return $this;
        }

        $controllerInstance = is_string($controller) ? new $controller() : $controller;
        $reflection = new ReflectionClass($controllerInstance);

        $groupAttr = $reflection->getAttributes(Group::class)[0] ?? null;
        $middlewareAttr = $reflection->getAttributes(Middleware::class);

        $routePrefix = $groupAttr?->newInstance()->prefix ?? null;
        $classMiddlewares = $middlewareAttr ? $middlewareAttr[0]->newInstance()->middlewares : [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $routeAttr = $method->getAttributes(Route::class)[0] ?? null;
            if (!$routeAttr) {
                continue;
            }

            $route = $routeAttr->newInstance();
            $methodMiddlewareAttr = $method->getAttributes(Middleware::class);
            $methodMiddlewares = $methodMiddlewareAttr ? $methodMiddlewareAttr[0]->newInstance()->middlewares : [];

            $fullPath = $routePrefix ? $routePrefix . $route->path : $route->path;
            $fullPath = rtrim($fullPath, "/");

            $handler = [$controllerInstance, $method->getName()];
            $routeDef = $this->map($route->method, $fullPath, $handler);

            // Merge class + method middlewares
            $mergedMiddlewares = array_merge($classMiddlewares, $methodMiddlewares);
            if (!empty($mergedMiddlewares)) {
                $routeDef->middlewares($mergedMiddlewares);
            }

            $this->roads[] = [
                "method" => $route->method,
                "path" => $fullPath,
                "handler" => [
                    "controller" => get_class($controllerInstance),
                    "method" => $method->getName(),
                ],
                "middlewares" => $mergedMiddlewares,
            ];
        }

        return $this;
    }

    public function run(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $acceptsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        // Redirect paths with trailing slashes (except root)
        if ($path !== '/' && str_ends_with($path, '/')) {
            $trimmedPath = rtrim($path, '/');
            $uri = $request->getUri()->withPath($trimmedPath);
            return new Response(301, ['Location' => (string) $uri]);
        }


        try {
            if (!$this->routesCached) {
                $this->cache->save($this->cacheItem->set($this->roads));
            }

            return $this->dispatch($request);

        } catch (NotFoundException $e) {
            // Custom 404 handling
            error_log("Route not found: " . $e->getMessage());

            return $acceptsJson
                ? json(['error' => 'Route not found'], 404)
                : view('/errors/404', Layout::Error);

        } catch (Exception $e) {
            // Generic 500 error fallback
            error_log("Routing failed: " . $e);

            return $acceptsJson
                ? json(['error' => 'Something went wrong'], 500)
                : view('/errors/500', Layout::Error);
        }
    }


}
