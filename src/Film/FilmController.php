<?php
namespace App\Film;

use App\Attributes\Roles;
use App\Attributes\Route;
use App\Entities\Role;
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
        $this->filmService = new FilmService($_ENV["HLS_DIR"]);

        $this->films = $this->filmService->getAllFilms();
        $this->base_url = __DIR__."/../../";
    }

    #[Route("GET", "")]
    public function listFilms()
    {
        $films = $this->filmService->getAllVideos();
        return view(view: "/film/list", data: $films);
    }

    #[Route("GET", "/upload")]
    #[Roles(array(Role::Admin))]
    public function upload()
    {
        return view(view: '/film/upload');
    }
 
    #[Route("GET", "/details/[a:token]")]
    public function details($token)
    {
        if (isset($token['token'])) {
            $video = $this->filmService->getVideoByToken($token);
            if (!$video) {
                die("Video not found.");
            }

            header('Content-Type: application/json');
            print_r(json_encode($video));
        }
    }

    // Route for handling the video upload and HLS conversion
    #[Route("POST", "/upload")]
    #[Roles(array(Role::Admin))]
    public function uploadVideo()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST" || !isset($_FILES["video"]) || !isset($_FILES["cover"])) {
            die("Invalid request.");
        }

        $videoFile = $_FILES["video"];
        $coverFile = $_FILES["cover"];

        try {
            $videoToken = $this->filmService->handleVideoUpload($videoFile, $coverFile);
            echo "Video uploaded. <a href='/films/watch/$videoToken'>Watch here</a>";
        } catch (\Exception $e) {
            die("Upload failed: " . $e->getMessage());
        }
    }
    
    #[Route("GET", "/watch/[a:token]")]
    public function watchVideo($token)
    {
       if(isset($token["token"])){
        $video = $this->filmService->getVideoByToken($token);
        if (!$video) {
            die("Video not found.");
        }

        return view(view: "/film/watch", data: $video);
       }
    }
}