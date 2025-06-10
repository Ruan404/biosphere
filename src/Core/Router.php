<?php
namespace App\Core;

use AltoRouter;
use App\Auth\AuthService;
use App\Entities\Layout;
use ReflectionClass;
use App\Attributes\{
    Route
};
use function App\Helpers\view;

class Router
{
    private AltoRouter $router;
    private $authService;

    public function __construct()
    {
        $this->router = new AltoRouter();
        $this->authService = new AuthService();
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

    public function registerController($controller)
    {
        $reflection = new ReflectionClass($controller);

        $routeAttributes = $reflection->getAttributes(Route::class);

        $prefix = '';

        if (!empty($routeAttributes)) {
            $prefix = $routeAttributes[0]->newInstance()->path;
        }

        foreach ($reflection->getMethods() as $method) {

            $attributes = $method->getAttributes(Route::class);

            if (empty($attributes)) {
                continue; //reviens au début de la boucle sans exécuter la suite
            }

            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();
                $fullRoute = $prefix . $route->path;
                $httpMethod = $route->method;
                $this->router->map($httpMethod, $fullRoute, [
                    'controller' => $controller, //controller class
                    'action' => $method->getName(), //method name,
                    "route" => $fullRoute,
                    "method" => $httpMethod
                ]);
            }
        }

        return $this;
    }

    public function run()
    {
        $match = $this->router->match();
        $target = $match['target'] ?? null;

        if (!$target) {
            return view("/errors/404", Layout::Error);
        }

        if (empty($_SESSION["role"]) && $_SERVER["REQUEST_URI"] !== "/login" && $_SERVER["REQUEST_URI"] !== "/signup") {
            header("Location: /login");
            exit;
        }

        if (!empty($_SESSION["role"])) {
            $sub = (object) [
                "Role" => $_SESSION["role"]
            ];

            if (!$this->authService->canAccessRoute($sub, $target["route"], $target["method"])) {
                header("HTTP/1.1 403 Forbidden");
                echo "Access denied";
                exit;
            }
        }

        return $this->handle($target, $match);
    }

}