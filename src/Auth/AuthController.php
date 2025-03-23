<?php
namespace App\Auth;

use App\Entities\Layout;
use \App\User\{
    User
};

use App\Attributes\Route;
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
        if (!empty($_POST)) {

            //instantier la class user
            $loginUser = new User();
            $loginUser->pseudo = $_POST['pseudo'];
            $loginUser->mdp = $_POST['password'];

            $user = $this->authService->login($loginUser);

            if ($user) {
                header('Location: /'); //le homeController prend le relais
                exit();
            }
        }

        return view(view: 'auth/login', layout: $this->layout, data: ['error' => true]);
    }

    #[Route(method: "GET", path: "/signup")]
    public function signupPage()
    {
        return view(view: 'auth/signup', layout: $this->layout);
    }

    #[Route(method: "POST", path: "/signup")]
    public function signup()
    {
        if (!empty($_POST)) {

            //create a new user
            $signupUser = new User();
            $signupUser->pseudo = $_POST['pseudo'];
            $signupUser->mdp = $_POST['password'];

            //essayer d'inscrire l'utilisateur
            $result = $this->authService->signup($signupUser);

            if ($result == true) {
                header('Location: /login');
                exit();
            }

            return view(view: 'auth/signup', layout: $this->layout, data: ['error' => true]);
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