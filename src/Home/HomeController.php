<?php
namespace App\Home;
use App\Attributes\{
    Route
};

use App\Auth\AuthService;

use App\Helpers\{
    Text,
    Page
};

class HomeController
{
    #[Route(method: "GET", path: "/")]
    public function index()
    {
        $user = AuthService::getUserSession();

        //l'utilisiteur n'est pas connecté
        if($user == null){
           header('Location: /login');
           exit();
        }

        $profile = Text::getFirstStr($user->pseudo);

        return Page::print(view: 'home/index', infos: ['profile'=> $profile]);

    }
}
