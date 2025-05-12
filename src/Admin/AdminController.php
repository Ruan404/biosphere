<?php

namespace App\Admin;

use App\Attributes\Roles;
use App\Attributes\Route;
use App\Admin\AdminService;
use App\Entities\Layout;
use App\Entities\Role;
use App\Exceptions\HttpExceptionInterface;
use App\Film\FilmService;
use App\Helpers\Response;
use App\Topic\TopicService;
use App\User\UserService;
use ErrorException;
use Exception;
use function App\Helpers\view;
ini_set('max_execution_time', 300);

#[Roles(array(Role::Admin))]
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
    public function index()
    {
        return view(view: "/admin/index", layout: Layout::Admin);
    }

    #[Route("GET", "/admin/[*:tab]")]
    public function getData($params)
    {
        try {
            $tab = htmlspecialchars($params["tab"]);
            switch ($tab) {
                case "users":
                    $users = new UserService()->getUsersExceptOne($_SESSION['user_id']);
                    return new Response()->json(['label' => 'utilisateurs', 'data' => $users]);

                case "topics":
                    $topics = new TopicService()->adminTopics();
                    return new Response()->json(['label' => 'topics', 'data' => $topics]);

                case "films":
                    $films = new FilmService()->adminFilms();

                    return new Response()->json(['label' => 'topics', 'data' => $films]);

                default:
                    return new Response()->json([]);
            }
        } catch (Exception $e) {
            error_log("Admin panel load failed: " . $e->getMessage());
            return new Response()->json(["success" => false, "message" => "impossible de charger la page"]);
        }
    }

    #[Route("GET", "/admin/film/upload")]
    public function upload()
    {
        return view(view: '/film/upload', layout: Layout::Admin);
    }

    // Route for handling the video upload and HLS conversion
    #[Route("POST", "/film/upload")]
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
    public function handleActions()
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);

            $action = $data['action'];
            $item = $data['item']['id'] ?? null;

            switch ($action) {
                case 'delete_user':
                    $this->adminService->deleteUser($item);
                case 'promote_user':
                    $this->adminService->promoteUser($item);
                    break;
                case 'delete_topic':
                    $this->adminService->deleteTopic(topic: $item);
                    break;
                case 'delete_podcast':
                    $this->adminService->deletePodcast($item);
                    break;
                case 'add_topic':
                    $this->adminService->addTopic($item);
                    break;
                case 'delete_film':
                    $this->adminService->deleteFilm($item);
                    break;
                default:
                    return new Response()->json(["success" => false, "message" => "l'action n'a pas pu aboutir"]);
            }

            return new Response()->json(["success" => true, "message" => "action menée avec success"]);
        } catch (HttpExceptionInterface $e) {
            http_response_code($e->getStatusCode());
            return new Response()->json(["success" => false, "message" => "l'action n'a pas pu aboutir"], $e->getStatusCode());
        } catch (Exception $e) {
            error_log("Admin action failed: " . $e->getMessage());
            return new Response()->json(["success" => false, "message" => "l'action n'a pas pu aboutir"], 500);
        }
    }
}