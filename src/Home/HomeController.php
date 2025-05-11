<?php
namespace App\Home;
use App\Attributes\{
    Route
};

use App\Attributes\Middleware;
use App\Middleware\IsLoggedInMiddleware;
use function App\Helpers\view;

#[Middleware(new IsLoggedInMiddleware())]
class HomeController
{
    #[Route(method: "GET", path: "/")]
    public function index()
    {
        return view(view: 'home/index');
    }
}
