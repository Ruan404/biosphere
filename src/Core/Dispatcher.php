<?php

namespace App\Core;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Dispatcher implements RequestHandlerInterface
{

    private $middlewares;

    private $index = 0;

    private $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->middlewares[$this->index] ?? null;

        $this->index++;

        if ($middleware === null) {
            return $this->response;
        }

        return $middleware->process($request, $this);
    }

    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

}