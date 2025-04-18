<?php
namespace App\Auth;

use App\Entities\Layout;
use App\Exceptions\HttpExceptionInterface;
use \App\User\{
    User
};

use App\Attributes\Route;
use Exception;
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
    public function loginPage()
    {
        return view(view: 'auth/login', layout: $this->layout);
    }

    #[Route(method: "POST", path: "/login")]
    public function login()
    {
        try {
            if (!empty($_POST)) {

                //instantier la class user
                $loginUser = new User($_POST['pseudo'], $_POST['password']);

                $user = $this->authService->login($loginUser);

                if ($user->role === 'admin') {
                    header('Location: /admin'); // Redirige vers l'espace admin
                    exit();
                } else {
                    header('Location: /'); // Redirige vers l'accueil normal
                    exit();
                }
            }
        } catch (HttpExceptionInterface $e) {
            // return new Response()->json([$e->getMessage()], $e->getStatusCode());
            return view(view: 'auth/login', layout: $this->layout, data: ['error' => true]);

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Clean);
        }
    }

    #[Route(method: "GET", path: "/signup")]
    public function signupPage()
    {
        return view(view: 'auth/signup', layout: $this->layout);
    }

    #[Route(method: "POST", path: "/signup")]
    public function signup()
    {
        try {
            if (!empty($_POST)) {

                //create a new user
                $signupUser = new User($_POST['pseudo'], $_POST['password']);

                //essayer d'inscrire l'utilisateur
                $this->authService->signup($signupUser);

                header('Location: /login');
                exit();
            }
        } catch (HttpExceptionInterface $e) {
            return view(view: 'auth/signup', layout: $this->layout, data: ['error' => true]);
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Clean);
        }
    }

    #[Route(method: "GET", path: "/logout")]
    public function logout()
    {
        $this->authService::logout();

        header('Location: /login');
        exit();
    }
}