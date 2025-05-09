<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use GuzzleHttp\Psr7\Response;

class CsrfMiddleware implements MiddlewareInterface
{
    private string $sessionKey = '_csrf_token';
    private string $formKey = '_csrf';

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Start session if not already started
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // Ensure token is in session
        if (empty($_SESSION[$this->sessionKey])) {
            $_SESSION[$this->sessionKey] = bin2hex(random_bytes(32));
        }

        // Attach token to request for controllers/templates
        $request = $request->withAttribute('csrf_token', $_SESSION[$this->sessionKey]);

        // Validate on unsafe methods
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $params = $request->getParsedBody() ?? [];

            if (
                !isset($params[$this->formKey]) ||
                !hash_equals($_SESSION[$this->sessionKey], $params[$this->formKey])
            ) {
                return new Response(403, ['Content-Type' => 'text/plain'], 'Invalid CSRF token');
            }
        }

        return $handler->handle($request);
    }
}
