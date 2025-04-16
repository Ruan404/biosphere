<?php
namespace App\Film;

use App\Attributes\Route;
use App\Helpers\Response;
use Exception;
use function App\Helpers\view;
use App\Film\FilmService;

#[Route("GET", "/films")]
class FilmController
{
    private $filmService;
    private $films;

    public function __construct()
    {
        //instancier la classe FilmService
        $this->filmService = new FilmService();
    }

    #[Route("GET", "")]
    public function index()
    {
        try {
            $this->films = $this->filmService->getAllFilms();
            return view(view: "/film/list", data: $this->films);

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            header("Location /errors/500");
        }
    }

    #[Route("GET", "/details/[*:token]")]
    public function details($token)
    {
        try {
            $video = $this->filmService->getFilmByToken($token['token']);
            if ($video === null) {

                return new Response()->json(["error" => "the video was not found"]);
            }
            return new Response()->json($video, 200);
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            header("Location /errors/500");
        }
    }

    #[Route("GET", "/watch/[*:token]")]
    public function watchVideo($token)
    {
        try {
            $video = $this->filmService->getFilmByToken($token["token"]);
            if ($video === null) {
                return view(view: "/errors/404", data: ["error" => "video was not found"]);
            }
            return view(view: "/film/watch", data: $video);
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            header("Location /errors/500");
        }
    }
}