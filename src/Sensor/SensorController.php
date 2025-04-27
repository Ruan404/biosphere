<?php
namespace App\Sensor;

use App\Attributes\Middlewares;
use App\Attributes\Route;
use App\Core\Middleware\IsLoggedIn;
use function App\Helpers\view;

#[Middlewares([IsLoggedIn::class])]
Class SensorController{
    
    #[Route("GET", "/sensors")]
    public function index(){

        view("/sensors/index");
        
    }
}