<?php
namespace App\Home;
use App\Attributes\{
    Route
};

use function App\Helpers\view;


class HomeController
{
    #[Route(method: "GET", path: "/")]
    public function index()
    {
        return view(view: 'home/index');

    }
}
