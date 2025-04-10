<?php

namespace App\Admin;

use App\Attributes\Roles;
use App\Attributes\Route;
use App\Admin\AdminService;
use App\Entities\Layout;
use App\Entities\Role;
use App\Film\FilmService;
use App\Topic\TopicService;
use App\User\UserService;
use function App\Helpers\view;
ini_set('max_execution_time', 300);


class AdminController
{
    private $adminService;
    private $filmService;

    public function __construct()
    {
        $this->adminService = new AdminService();
        $this->filmService = new FilmService();
    }

    #[Route("GET", "/admin")]
    #[Roles(array(Role::Admin))]
    public function index()
    {
        $users = new UserService()->getUsers();
        $topics = new TopicService()->getAllTopics();
        $films = new FilmService()->getAllFilms();

        return view(view: "/admin/index", data: ['users' => $users, 'topics' => $topics, 'films' => $films], layout: Layout::Admin);
    }

    #[Route("GET", "/admin/film/upload")]
    #[Roles(array(Role::Admin))]
    public function upload()
    {
        return view(view: '/film/upload', layout: Layout::Admin);
    }

    // Route for handling the video upload and HLS conversion
    #[Route("POST", "/film/upload")]
    #[Roles(array(Role::Admin))]
    public function uploadFilm()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            die("Invalid request.");
        }

        
        // Check the file type (either 'video' or 'cover')
        $type = isset($_POST['type']) ? $_POST['type'] : null;
        if ($type === 'video') {
            // Handle the video chunk upload
            if (isset($_FILES['file'])) {
                $videoFile = $_FILES['file'];
                $chunkNumber = isset($_POST['chunkNumber']) ? (int) $_POST['chunkNumber'] : 0;
                $totalChunks = isset($_POST['totalChunks']) ? (int) $_POST['totalChunks'] : 0;

                try {
                    $uniqueToken = $this->filmService->handleChunkedUpload($videoFile, $chunkNumber, $totalChunks);
                    echo "Chunk uploaded successfully. Token: " . $uniqueToken;
                } catch (\Exception $e) {
                    die("Upload failed: " . $e->getMessage());
                }
            }
        } elseif ($type === 'cover') {
            // Handle the cover image upload
            if (isset($_FILES['file'])) {
                $coverFile = $_FILES['file'];
                try {
                    $coverPath = $this->filmService->handleCoverImageUpload($coverFile);
                    echo "Cover image uploaded successfully.";
                } catch (\Exception $e) {
                    die("Upload failed: " . $e->getMessage());
                }
            }
        } else {
            die("Invalid file type.");
        }
    }


    #[Route("POST", "/admin/action")]
    #[Roles(array(Role::Admin))]
    public function handleActions()
    {
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
            $pseudo = $_POST['pseudo'] ?? null;
            $topic = $_POST['topic'] ?? null;
            $podcast = $_POST['podcast'] ?? null;
            $film = $_POST['film'] ?? null;

            switch ($action) {
                case 'delete_user':
                    if ($pseudo) {
                        $this->adminService->deleteUser($pseudo);
                        // Redirect or show confirmation
                    }
                    break;

                case 'promote_user':
                    if ($pseudo) {
                        $this->adminService->promoteUser($pseudo);
                        // Redirect or show confirmation
                    }
                    break;

                case 'delete_topic':
                    if ($topic) {
                        $this->adminService->deleteTopic($topic);
                        // Redirect or show confirmation
                    }
                    break;

                case 'delete_podcast':
                    if ($podcast) {
                        $this->adminService->deletePodcast($podcast);
                        // Redirect or show confirmation
                    }
                    break;

                case 'add_topic':
                    if ($topic) {
                        $message = $this->adminService->addTopic($topic);  // Appeler la méthode addTopic
                        // Redirect or show confirmation
                    }
                    break;
                case 'delete_film':
                    if ($film) {
                        $this->adminService->deleteFilm($film);
                        // Redirect or show confirmation
                    }
                    break;

                default:
                    // Handle unknown actions
                    echo 'Action not found';
                    break;
            }

            header("location: /admin");
        }
    }
}