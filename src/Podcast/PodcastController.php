<?php
namespace App\Podcast;

use App\Attributes\Route;
use App\Helpers\Page;

#[Route('GET','/podcast')]
Class PodcastController{

    #[Route('GET','')]
    public function index(){

        return Page::print("/podcast/index");
    }
}