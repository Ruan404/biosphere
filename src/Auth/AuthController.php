<?php
namespace App\Auth;

use App\Entities\Layout;
use \App\User\{
    User,
    UserService
};

use App\Helpers\Page;

use App\Attributes\Route;

class AuthController
{

    private Layout $layout = Layout::Auth;
    private $authService = new AuthService();

    #[Route(method: "GET", path: "/login")]
    public function loginPage()
    {
        Page::print(view: 'auth/login', layout: $this->layout);
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
                header('Location: /' ); //le homeController prend le relais
                exit();
            }
        }

        Page::print(view: 'auth/login', layout: $this->layout, infos: ['error' => true]);
    }

    #[Route(method: "GET", path: "/signup")]
    public function signupPage()
    {

    }

    #[Route(method: "POST", path: "/signup")]
    public function signup()
    {
    }
}