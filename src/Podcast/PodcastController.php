<?php
namespace App\Podcast;

use App\Attributes\Group;
use App\Attributes\Middleware;
use App\Attributes\Route;
use App\Middleware\IsLoggedInMiddleware;
use function App\Helpers\view;

#[Middleware(new IsLoggedInMiddleware())]
#[Group('/podcast')]
Class PodcastController{

    #[Route('GET','/')]
    public function index(){

        return view("/podcast/index");
    }
}