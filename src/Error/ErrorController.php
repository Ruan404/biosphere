<?php

namespace App\Error;

use App\Attributes\Route;
use function App\Helpers\view;

Class ErrorController{
    
    #[Route("GET", "/404")]
    public function page404(){
        return view("/errors/404");
    }

    #[Route("GET", "/500")]
    public function page500(){
        return view("/errors/500");
    }
}