<?php
namespace App\Podcast;

use App\Attributes\Route;
use function App\Helpers\view;

#[Route('GET','/podcast')]
Class PodcastController{

    #[Route('GET','')]
    public function index(){

        return view("/podcast/index");
    }
}