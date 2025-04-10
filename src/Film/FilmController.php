<?php
namespace App\Film;

use App\Attributes\Route;
use function App\Helpers\view;
use App\Film\FilmService;
use Dotenv\Dotenv;

#[Route("GET", "/films")]
class FilmController
{
    private $filmService;
    private $films;
    private $db;
    private $base_url;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        //instancier la classe FilmService
        $this->filmService = new FilmService();

        $this->films = $this->filmService->getAllFilms();
        $this->base_url = __DIR__."/../../";
    }

    #[Route("GET", "")]
    public function listFilms()
    {
        return view(view: "/film/list", data: $this->films);
    }
 
    #[Route("GET", "/details/[a:token]")]
    public function details($token)
    {
        if (isset($token['token'])) {
            $video = $this->filmService->getFilmByToken($token['token']);
            if (!$video) {
                die("Video not found.");
            }

            header('Content-Type: application/json');
            print_r(json_encode($video));
        }
    }
    
    #[Route("GET", "/watch/[a:token]")]
    public function watchVideo($token)
    {
       if(isset($token["token"])){
        $video = $this->filmService->getFilmByToken($token["token"]);
        if (!$video) {
            die("Video not found.");
        }

        return view(view: "/film/watch", data: $video);
       }
    }
}