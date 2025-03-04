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
        return Page::print(view: 'home/index');

    }
}
