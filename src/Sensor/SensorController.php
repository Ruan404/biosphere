<?php
namespace App\Sensor;

use App\Attributes\Middleware;
use App\Attributes\Route;
use App\Middleware\IsLoggedInMiddleware;
use function App\Helpers\view;

#[Middleware(new IsLoggedInMiddleware())]
Class SensorController{
    
    #[Route("GET", "/sensors")]
    public function index(){

        return view("/sensors/index");
        
    }
}