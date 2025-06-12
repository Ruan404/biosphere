<?php

namespace App\Middleware;

use App\Auth\AuthService;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AccessControlMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path   = $request->getUri()->getPath();
        $method = $request->getMethod();
        $accept = $request->getHeaderLine('Accept');

        // Check if user is authenticated
        $role = $_SESSION['role'] ?? null;
        if (!$role) {
            return $this->respond($accept, '/login', 'Utilisateur non connecté', 401);
        }

        $sub = (object) ['Role' => $role];

        // Check authorization with Casbin
        if (!$this->authService->canAccessRoute($sub, $path, $method)) {
            return $this->respond($accept, '/', 'Accès refusé', 403);
        }

        return $handler->handle($request);
    }

    private function respond(string $accept, string $redirectUrl, string $message, int $status): ResponseInterface
    {
        if (str_contains($accept, 'application/json')) {
            return new JsonResponse(['success' => false, 'message' => $message], $status);
        }

        return new RedirectResponse($redirectUrl, $status);
    }
}
