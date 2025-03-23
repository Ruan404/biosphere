<?php
namespace App\Film;

use App\Attributes\Route;
use function App\Helpers\view;
use App\Film\FilmService;


#[Route("GET", "/films")]
class FilmController
{
    private $films;

    public function __construct()
    {
        $this->films = new FilmService()->getAllFilms();
    }

    #[Route("GET", "")]
    public function index()
    {
        return view(view: '/film/index', data: ['films' => $this->films]);
    }


    #[Route("GET", "/details/[*:slug]")]
    public function details($params)
    {
        if (isset($params['slug'])) {
            //instancier la classe FilmService
            $film = new FilmService();

            $filmDetails = $film->getFilmByTitle($params['slug']);

            $filmJson = $filmJson = json_encode($filmDetails);

            echo $filmJson;
        }
    }

    #[Route("GET", "/[*:slug]")]
    public function viewFilm($params)
    {
        if (isset($params['slug'])) {
            //instancier la classe FilmService
            $film = new FilmService();

            $filmDetails = $film->getFilmByTitle($params['slug']);

            return view(view: '/film/show', data: ['films' => $filmDetails]);
        }
    }
}