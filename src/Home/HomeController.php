<?php
namespace App\Home;
use App\Attributes\{
    Route
};

use App\Attributes\Middlewares;
use App\Core\Middleware\IsLoggedIn;
use function App\Helpers\view;

#[Middlewares([IsLoggedIn::class])]
class HomeController
{
    #[Route(method: "GET", path: "/")]
    public function index()
    {
        return view(view: 'home/index');

    }
}
