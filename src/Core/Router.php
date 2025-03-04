<?php
namespace App\Core;

use AltoRouter;
use ReflectionClass;
use App\Attributes\{
    Route
};

class Router
{
    private AltoRouter $router;

    public function __construct()
    {
        $this->router = new AltoRouter();
    }

    public function registerController($controller)
    {
        $reflection = new ReflectionClass($controller);

        $routeAttributes = $reflection->getAttributes();

        $prefix = '';

        if(!empty($routeAttributes)){
            $prefix = $routeAttributes[0]->newInstance()->path;
        }

        foreach ($reflection->getMethods() as $method) {

            $attributes = $method->getAttributes(Route::class);

            if(empty($attributes)){
                continue; //reviens au début de la boucle sans exécuter la suite
            }

            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();

                $this->router->map($route->method, $prefix.$route->path, [
                    'controller' => $controller, //controller class
                    'action' => $method->getName(), //method name
                ]);
            }
        }
    }

    public function run()
    {
        $match = $this->router->match();

        $target = $match['target'] ?? null;
        
        $controller = new $target['controller'](); //instantiate the controller class
        
        $action = $target['action']; //get the class method

        //there is some parameters in the url
        if ($match['params'] != null) {
            //use the method with params
            $controller->$action(params: $match['params']);
        } else {
            $controller->$action(); //use the method
        }

    }
}