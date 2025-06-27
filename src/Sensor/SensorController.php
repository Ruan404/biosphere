<?php
namespace App\Sensor;

use App\Attributes\Route;
use function App\Helpers\view;

Class SensorController{
    
    #[Route("GET", "/sensors")]
    public function index(){

        return view("/sensors/index");
        
    }
}