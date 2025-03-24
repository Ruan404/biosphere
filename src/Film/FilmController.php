<?php
namespace App\Film;

use App\Attributes\Roles;
use App\Attributes\Route;
use App\Entities\Role;
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
        
        $this->films = $this->filmService->getAllFilms();
    }

    #[Route("GET", "")]
    public function index()
    {
        return view(view: '/film/index', data: ['films' => $this->films]);
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

            echo $filmJson;
        }
    }

    #[Route("GET", "/[*:slug]")]
    public function viewFilm($params)
    {
        if (isset($params['slug'])) {
            $filmDetails = $this->filmService->getFilmByTitle($params['slug']);

            return view(view: '/film/show', data: ['films' => $filmDetails]);
        }
    }


    // Route action to upload a video
    #[Route("POST", "/upload")]
    #[Roles(array(Role::Admin))]
    public function uploadVideo()
    {
        try {
            if (!isset($_FILES['video'])) {
                throw new \Exception('No file uploaded.');
            }

            $filmService = new FilmService();
            $videoPath = $filmService->uploadVideo($_FILES['video']);
            $mpdFile = $filmService->generateDASH($videoPath);

            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Video uploaded and processed successfully.',
                'mpd_url' => '/uploads/dash/' . $mpdFile
            ]);
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // Route action to serve the MPD file
    #[Route('GET', '/video/manifest.mpd')]
    public function serveMPD()
    {
        $mpdFile = __DIR__ . '/../../public/uploads/dash/your_file_name.mpd';  // Adjust file name
        if (file_exists($mpdFile)) {
            header('Content-Type: application/xml');
            readfile($mpdFile);
        } else {
            echo "MPD file not found!";
        }
    }

    // Route action to serve video segments
    #[Route('GET', '/video/[a:segment].mp4')]
    public function serveVideoSegment($segment)
    {
        $segmentFile = __DIR__ . '/../../public/uploads/dash/video' . $segment . '.mp4';
        if (file_exists($segmentFile)) {
            header('Content-Type: video/mp4');
            readfile($segmentFile);
        } else {
            echo "Video segment not found!";
        }
    }

}