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
 
    #[Route("GET", "/details/[*:slug]")]
    public function details($params)
    {
        if (isset($params['slug'])) {
            $filmDetails = $this->filmService->getFilmByTitle($params['slug']);

            $filmJson = $filmJson = json_encode($filmDetails);

            header('Content-Type: application/json');
            echo $filmJson;
        }
    }

    // Route for handling the video upload and HLS conversion
    #[Route("POST", "/upload")]
    #[Roles(array(Role::Admin))]
    public function uploadVideo()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["video"])) {
            $videoFile = $_FILES["video"];
            $ext = pathinfo($videoFile["name"], PATHINFO_EXTENSION);

            if (!in_array(strtolower($ext), ["mp4", "mov", "avi"])) {
                die("Invalid file type.");
            }

            $uniqueToken = $this->filmService->generateUniqueToken();
            $filePath = $this->base_url.$_ENV['UPLOAD_DIR'] . $uniqueToken . "." . $ext;
            move_uploaded_file($videoFile["tmp_name"], $filePath);

            $hlsFolder = $this->base_url.$_ENV['HLS_DIR'] . $uniqueToken;
            $playlistPath = $this->filmService->processHLS($filePath, $hlsFolder);

            $this->filmService->insertVideo($videoFile["name"], $filePath, $playlistPath, $uniqueToken);
            
            echo "Video uploaded. <a href='/films/watch/$uniqueToken'>Watch here</a>";
        }
    }


    #[Route("GET", "/watch/[a:token]")]
    public function watchVideo($token)
    {
        $video = $this->filmService->getVideoByToken($token);
        if (!$video) {
            die("Video not found.");
        }

        return view(view: "/film/watch", data: $video);
    }
}