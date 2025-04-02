<?php

namespace App\Message;

use App\Attributes\Route;
use App\Helpers\Page;

#[Route("GET", "/messagerie")]
Class MessageController{

    #[Route("GET", "")]
    public function index(){
        return view("/message/index");
    }
}