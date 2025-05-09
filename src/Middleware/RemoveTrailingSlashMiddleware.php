<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Psr7\Response;

class RemoveTrailingSlashMiddleware implements MiddlewareInterface
{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        

        // Ignore root (/) but redirect /something/ -> /something
        if ($path !== '/' && str_ends_with($path, '/')) {
            $newUri = $uri->withPath(rtrim($path, '/'));
            return new Response(301, ['Location' => (string)$newUri]);
        }

        return $handler->handle($request);
    }
}
