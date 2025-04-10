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

        $type = $_POST['type'] ?? null;
        $token = $_POST['token'] ?? null;

        if (!$token || $type !== 'video') {
            http_response_code(400);
            die("Invalid upload data.");
        }

        if (isset($_FILES['file'])) {
            $videoFile = $_FILES['file'];
            $chunkNumber = (int) ($_POST['chunkNumber'] ?? 0);
            $totalChunks = (int) ($_POST['totalChunks'] ?? 0);
            $filename = $_POST['filename'] ?? 'video.mp4';

            // Handle optional cover upload with final chunk
            $coverFile = $_FILES['cover'] ?? null;

            try {
                $this->filmService->handleChunkedUpload($videoFile, $chunkNumber, $totalChunks, $filename, $token, $coverFile);
                echo "Chunk $chunkNumber uploaded successfully.";
            } catch (\Exception $e) {
                http_response_code(500);
                die("Upload failed: " . $e->getMessage());
            }
        } else {
            http_response_code(400);
            die("Missing video file.");
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