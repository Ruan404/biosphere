<?php
namespace App\Home;
use App\Attributes\{
    Route
};

use App\Entities\Roles;

class HomeController
{

    #[Route(method: "GET", path: "/")]
    public function index()
    {
        echo <<<HTML
            <h1>home</h1>
        HTML;
    }
}
