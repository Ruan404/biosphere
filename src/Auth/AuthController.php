<?php 
namespace App\Auth;

use App\Entities\Layout;
use \App\User\{
    User,
    UserService
};

use App\Helpers\Page;

use App\Attributes\Route;

Class AuthController{

    private Layout $layout = Layout::Auth;


    #[Route(method: "GET", path: "/login")]
    public function loginPage(){
        Page::print(view: 'auth/login', layout: $this->layout);
    }

    #[Route(method: "POST", path: "/login")]
    public function login(){
        
    }

    #[Route(method: "GET", path: "/signup")]
    public function signupPage(){

    }

    #[Route(method: "POST", path: "/signup")]
    public function signup(){

    }
}