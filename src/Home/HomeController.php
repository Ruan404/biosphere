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

        if ($user) {
            $profile = Text::getFirstStr($user->pseudo);

            Page::print(view: 'home/index', infos: ['profile'=> $profile]);
        }

        //l'utilisiteur n'est pas connecté
        elseif($user == null){
           header('Location: /login');
           exit();
        }

    }
}
