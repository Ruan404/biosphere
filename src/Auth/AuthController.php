<?php
namespace App\Auth;

use App\Entities\Layout;
use App\Exceptions\HttpExceptionInterface;
use App\Helpers\Csrf;
use \App\User\{
    User
};

use Dotenv\Dotenv;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use App\Attributes\Route;
use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UnexpectedValueException;
use function App\Helpers\json;
use function App\Helpers\view;


class AuthController
{

    private Layout $layout = Layout::Auth;
    private $authService;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

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
        } catch (UnexpectedValueException $e) {
            return view(view: 'auth/signup', layout: $this->layout, data: ['success' => false, "message" => $e->getMessage()]);

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


    #[Route(method: "GET", path: "/api/token")]
    public function getToken(ServerRequestInterface $request): ResponseInterface
    {

        $secret = $_ENV['JWT_SECRET'] ?? null;

        $token = $_SESSION['jwt_token'] ?? null;
        try {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        } catch (ExpiredException $e) {
            //refresh token
            $payload = [
                'sub' => $_SESSION['user_id'],
                'username' => $_SESSION['username'],
                'role' => $_SESSION['role'],
                'iat' => time(),
                'exp' => time() + $_ENV["TOKEN_DURATION"],
            ];

            $token = JWT::encode($payload, $secret, 'HS256');
            $_SESSION['jwt_token'] = $token;
            
        } catch (Exception) {
            return json(["error" => "unauthorize"], 401);
        }

        return json(['token' => $token], 200);
    }

}