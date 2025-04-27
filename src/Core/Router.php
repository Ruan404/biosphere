<?php
namespace App\Core;

use AltoRouter;
use App\Attributes\Middlewares;
use App\Entities\{
    Layout,
    Role
};
use ReflectionClass;
use App\Attributes\{
    Route,
    Roles
};
use function App\Helpers\view;

class Router
{
    private AltoRouter $router;


    public function __construct()
    {
        $this->router = new AltoRouter();
    }

    private function handle($target, $match)
    {
        $controller = new $target['controller'](); //instantiate the controller class
        $action = $target['action']; //get the class method

        //there is some parameters in the url
        if ($match['params'] != null) {
            //use the method with params
            $controller->$action($match['params']);
        } else {
            $controller->$action(); //use the method
        }

        return $this;
    }

    public function middleware($callable)
    {
        call_user_func($callable);

        return $this;
    }

    public function registerController($controller)
    {
        $reflection = new ReflectionClass($controller);
        $routeAttributes = $reflection->getAttributes(Route::class);
        $middlewareAttributes = $reflection->getAttributes(Middlewares::class);

        $prefix = '';

        $middlewares = [];

        if (!empty($middlewareAttributes)) {
            $attribute = $middlewareAttributes[0];
            $middlewares = $attribute->newInstance()->name;
        }

        if (!empty($routeAttributes)) {
            $prefix = $routeAttributes[0]->newInstance()->path;
        }

        foreach ($reflection->getMethods() as $method) {

            $attributes = $method->getAttributes(Route::class);
            $rolesAttributes = $method->getAttributes(Roles::class);

            if (empty($attributes)) {
                continue; //reviens au début de la boucle sans exécuter la suite
            }

            foreach ($attributes as $attribute) {
                $roles = $rolesAttributes ? $rolesAttributes[0]->newInstance()->roles : [];
                $route = $attribute->newInstance();

                $this->router->map($route->method, $prefix . $route->path, [
                    'controller' => $controller, //controller class
                    'action' => $method->getName(), //method name,
                    'roles' => $roles,
                    'middlewares' => $middlewares
                ]);
            }
        }

        return $this;
    }

    public function run()
    {
        $match = $this->router->match();
        $target = $match['target'] ?? null;

        if ($target === null) {
            return view("/errors/404", Layout::Error);
        }

        $roles = $target["roles"] ?? [];
        $middlewares = $target["middlewares"];
       
        if(!empty($middlewares)){
            foreach($middlewares as $middleware){
                (new $middleware)->handle();
            }
        }
        
        if (!empty($roles)) {
            if (in_array(Role::tryFrom($_SESSION['role']), $roles)) {

                $this->handle($target, $match);
            } else {
                header("Location: /");
                exit;
            }
        } elseif (empty($roles)) {
            $this->handle($target, $match);
        }

        return $this;
    }
}