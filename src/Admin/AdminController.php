<?php

namespace App\Admin;

use App\Attributes\Roles;
use App\Attributes\Route;
use App\Admin\AdminService;
use App\Entities\Layout;
use App\Entities\Role;
use App\Film\FilmService;
use App\Helpers\Response;
use App\Topic\TopicService;
use App\User\UserService;
use Exception;
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

        if (session_status() == 1) {
            session_start();
        }
    }

    #[Route("GET", "/admin")]
    #[Roles(array(Role::Admin))]
    public function index()
    {
        $users = new UserService()->getUsersExceptOne($_SESSION['user_id']);
        $topics = new TopicService()->getAllTopics();
        $films = new FilmService()->getAllFilms();

        return view(view: "/admin/index", data: ['users' => $users, 'topics' => $topics, 'films' => $films], layout: Layout::Admin);
    }

    #[Route("GET", "/admin/[*:tab]")]
    #[Roles(array(Role::Admin))]
    public function getData($params)
    {
        $tab = htmlspecialchars($params["tab"]);
        switch ($tab) {
            case "users":
                $users = new UserService()->getUsersExceptOne($_SESSION['user_id']);

                return new Response()->json(['label' => 'utilisateurs', 'data' => $users]);

            case "topics":
                $topics = new TopicService()->adminAllTopics();

                return new Response()->json(['label' => 'topics', 'data' => $topics]);

            default :
                return new Response()->json([]);
        }
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
        try {
            $token = $_POST['token'];
            $videoFile = $_FILES['file'];
            $chunkNumber = (int) ($_POST['step']);
            $totalChunks = (int) ($_POST['totalChunks']);
            $filename = $_POST['filename'];
        } catch (Exception) {
            return new Response()->json(["error" => "Invalid upload data."], 400);
        }

        try {
            $result = $this->filmService->chunkedUpload($videoFile, $chunkNumber, $totalChunks, $filename, $token);

            if ($result["state"] !== "done") {
                return new Response()->json(["message" => "chunk $chunkNumber téléchargé avec success."]);
            } else {
                try {
                    $title = $_POST['title'];
                    $description = $_POST['description'];
                    $coverFile = $_FILES['cover'];

                    $cover = $this->filmService->uploadImage($coverFile, $result["token"]);

                    if ($cover) {
                        $this->filmService->addFilm($title, $description, $result["path"], 'playlistPath', $cover, $result["token"]);
                        return new Response()->json(["message" => "téléchargement terminé"]);
                    }
                } catch (Exception $e) {
                    error_log("Video upload failed: " . $e->getMessage());
                    return new Response()->json(["error" => "une erreur s'est produite lors du téléchargement"]);
                }
            }
        } catch (Exception $e) {
            error_log("Chunk upload failed: " . $e->getMessage());
            return new Response()->json(["error" => "une erreur s'est produite lors du téléchargement"]);
        }
    }

    #[Route("POST", "/admin/action")]
    #[Roles(array(Role::Admin))]
    public function handleActions()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        // dd($data);
        if (isset($data)) {
            $action = $data['action'];
            $pseudo = $data['item']['pseudo'] ?? null;
            $topic = $data['item']['topic'] ?? null;
            $podcast = $data['item']['podcast'] ?? null;
            $film = $data['item']['film'] ?? null;

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

           return new Response()->json(["success"=> true, "message"=> "action menée avec success"]);
        }
    }
}