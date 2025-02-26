<?php
namespace App;

use AltoRouter;
use Exception;
class Router
{
    /**
     * @var string
     */
    private $viewPath; //chemin de la vue qu'on veut afficher

    /**
     * @var Altorouter
     */
    private $router; //initialiser le router


    //initialisation des variables
    public function __construct(string $viewPath)
    {
        $this->viewPath = $viewPath;
        $this->router = new AltoRouter(); //initialiser le router
    }

    public function url(string $name, array $params = []): ?string
    {
        return $this->router->generate($name, $params);
    }

    public function get(string $url, string $view, ?string $name = null): self
    {
        $this->router->map('GET', $url, $view, $name);

        return $this;
    }

    public function post(string $url, string $action, ?string $name = null): self
    {
        $this->router->map('POST', $url, $action, $name);

        return $this;
    }

    public function run(): self
    {
        $match = $this->router->match();

        //la page n'a pas été trouvée
        if (!$match) {
            ob_start();
            require $this->viewPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'errors/404.php';
            $content = ob_get_clean();
            require $this->viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR .'default.php';
            return $this;
        
        }

        $view = $match['target'];

        //verifier si la route contient 'auth'
        $isAuth = strpos($view, 'auth');
        $isApi = strpos($view, 'api');

        $params = $match['params']; //récupérer les paramètres
        $layout = $isAuth ? 'auth' : 'default'; //récupérer le bon layout

        //renvoyer le routeur courant et non l'objet router déclaré dans la classe
        $router = $this;

        if ($isApi) {
            require $this->viewPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
            return $this;
        }

        ob_start();
        require $this->viewPath . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view . '.php';
        $content = ob_get_clean();
        require $this->viewPath . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $layout . '.php';

        return $this;
    }
}