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
       
        $role = $_SESSION['role'] ?? "guest";
        $sub = (object) ['Role' => $role];

        // Debug: Log current role and path
        error_log("AccessControlMiddleware: Role=$role, Path=$path, Method=$method");

        if (!$this->authService->canAccessRoute($sub, $path, $method)) {
            error_log("AccessControlMiddleware: Access denied for role $role on $path");

            if ($role === 'guest') {
                // Redirect to login page with 302 (Found) status
                return $this->respond($accept, '/login', 'Veuillez vous connecter.', 302);
            } else {
                // Redirect to home page with 302 status
                return $this->respond($accept, '/', 'Accès refusé.', 302);
            }
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
