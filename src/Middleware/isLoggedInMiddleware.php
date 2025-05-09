<?php

namespace App\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class IsLoggedInMiddleware implements MiddlewareInterface{

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface{

        if(session_status() === 1){
            session_start();
        }
        //l'utilisiteur n'est pas connecté
        if(empty($_SESSION["username"])){
          return new Response(301, ['location' => '/login']);
        }

        return $handler->handle($request);
    }
}