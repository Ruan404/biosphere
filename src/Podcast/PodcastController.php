<?php
namespace App\Podcast;

use App\Attributes\Group;
use App\Attributes\Route;
use function App\Helpers\view;


#[Group('/podcast')]
Class PodcastController{

    #[Route('GET','/')]
    public function index(){

        return view("/podcast/index");
    }
}