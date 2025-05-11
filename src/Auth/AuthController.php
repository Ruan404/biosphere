<?php
namespace App\Auth;

use App\Entities\Layout;
use App\Exceptions\HttpExceptionInterface;
use App\Helpers\Csrf;
use \App\User\{
    User
};

use App\Attributes\Route;
use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use function App\Helpers\view;


class AuthController
{

    private Layout $layout = Layout::Auth;
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    #[Route(method: "GET", path: "/login")]
    public function loginPage($request)
    {
       
        return view(view: 'auth/login', layout: $this->layout, data: Csrf::get($request));
    }

    #[Route(method: "POST", path: "/login")]
    public function login(ServerRequestInterface $request)
    {
        try {
            $payload = $request->getParsedBody();

            //instantier la class user
            $loginUser = new User($payload['pseudo'], $payload['password']);

            $user = $this->authService->login($loginUser);

            if ($user->role === 'admin') {
                return new Response(301, ["location" => "/admin"]);
            } else {
                return new Response(301, ["location" => "/"]);
            }
        } catch (HttpExceptionInterface $e) {
            return view(view: 'auth/login', layout: $this->layout, data: ['error' => true], status: $e->getStatusCode());

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }

    #[Route(method: "GET", path: "/signup")]
    public function signupPage()
    {
        return view(view: 'auth/signup', layout: $this->layout);
    }

    #[Route(method: "POST", path: "/signup")]
    public function signup(ServerRequestInterface $request)
    {
        try {
            $payload = $request->getParsedBody();

            //create a new user
            $signupUser = new User($payload['pseudo'], $payload['password']);

            //essayer d'inscrire l'utilisateur
            $this->authService->signup($signupUser);

            return new Response(301, ["location" => "/login"]);

        } catch (HttpExceptionInterface $e) {
            return view(view: 'auth/signup', layout: $this->layout, data: ['error' => true], status: $e->getStatusCode());
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }

    #[Route(method: "GET", path: "/logout")]
    public function logout()
    {
        $this->authService::logout();

        return new Response(301, ["location" => "/login"]);

    }
}