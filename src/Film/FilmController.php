<?php
namespace App\Film;

use App\Attributes\Group;
use App\Attributes\Middleware;
use App\Attributes\Route;
use App\Entities\Layout;
use App\Middleware\IsLoggedInMiddleware;
use function App\Helpers\json;
use Exception;
use function App\Helpers\view;
use App\Film\FilmService;

#[Middleware(middlewares: new IsLoggedInMiddleware())]
#[Group("/films")]
class FilmController
{
    private $filmService;
    private $films;

    public function __construct()
    {
        //instancier la classe FilmService
        $this->filmService = new FilmService();
    }

    #[Route("GET", "/")]
    public function index()
    {
        try {
            $this->films = $this->filmService->getAllFilms();
            return view(view: "/film/list", data: $this->films);

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }

    #[Route("GET", "/details/{token}")]
    public function details($request)
    {
        try {
            $video = $this->filmService->getFilmByToken($request->getAttribute('token'));
            if ($video === null) {

                return json(["error" => "the video was not found"]);
            }
            return json($video, 200);
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }

    #[Route("GET", "/watch/{token}")]
    public function watchVideo($request)
    {

        try {
            $video = $this->filmService->getFilmByToken($request->getAttribute('token'));
            if ($video === null) {
                return view(view: "/errors/404", data: ["error" => "video was not found"]);
            }
            return view(view: "/film/watch", data: $video);
            
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }
}