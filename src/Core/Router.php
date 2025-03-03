<?php
namespace Core;

use AltoRouter;
use ReflectionClass;
use App\routeAttributes\{
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
        $methods = $reflection->getMethods();

        dd($methods);

        foreach ($methods as $method) {

            $attributes = $method->getAttributes(Route::class);

            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();

                $this->router->map($route->method, $route->path, [
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