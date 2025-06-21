<?php

namespace App\Admin;

use App\Attributes\Roles;
use App\Attributes\Route;
use App\Admin\AdminService;
use App\Entities\Role;
use App\Exceptions\HttpExceptionInterface;
use App\Film\FilmService;
use App\Helpers\Response;
use App\Topic\TopicService;
use App\User\Dto\UserAdminPanelDto;
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
    }

    #[Route("GET", "/admin")]
    public function index()
    {
        return view(view: "/admin/index");
    }


    #[Route("GET", "/admin/film/upload")]
    public function upload()
    {
        return view(view: '/film/upload');
    }

    #[Route("GET", "/admin/[*:tab]")]
    public function getData($params)
    {
        try {
            $tab = htmlspecialchars($params["tab"]);
            switch ($tab) {
                case "users":
                    return new Response()->json(new UserService()->getUsersExcludingId($_SESSION['user_id'], UserAdminPanelDto::class));

                case "topics":
                    return new Response()->json(new TopicService()->adminTopics());

                case "films":
                    return new Response()->json($this->filmService->adminFilms());

                default:
                    return new Response()->json([]);
            }
        } catch (Exception $e) {
            error_log("Admin panel load failed: " . $e->getMessage());
            return new Response()->json(["success" => false, "message" => "impossible de charger la page"]);
        }
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
            $action = $_POST['action'];
            $slug = $_POST['slug'] ?? "";
            $slugs = $_POST['slugs'] ?? [];

            switch ($action) {
                case 'delete_user':
                    $this->adminService->deleteUser($slug);
                    break;
                case 'delete_users':
                    $this->adminService->deleteUsers($slugs);
                    break;
                case 'promote_user':
                    $this->adminService->promoteUser($slug);
                    break;
                case 'delete_topic':
                    $this->adminService->deleteTopic( $slug);
                    break;
                 case 'delete_topics':
                    $this->adminService->deleteTopics($slugs);
                    break;
                case 'add_topic':
                    $this->adminService->addTopic($slug);
                    break;
                case 'delete_film':
                    $this->adminService->deleteFilm($slug);
                    break;
                case 'delete_films':
                    $this->adminService->deleteFilms($slugs);
                    break;
                 case 'delete_podcast':
                    $this->adminService->deletePodcast($slug);
                    break;
                default:
                    return new Response()->json(["success" => false, "message" => "l'action n'a pas pu aboutir"]);
            }

            return new Response()->json(["success" => true, "message" => "action menée avec success"]);
        } catch (HttpExceptionInterface $e) {
            return new Response()->json(["success" => false, "message" => "l'action n'a pas pu aboutir"], $e->getStatusCode());
        } catch (Exception $e) {
            error_log("Admin action failed: " . $e->getMessage());
            return new Response()->json(["success" => false, "message" => "l'action n'a pas pu aboutir"], 500);
        }
    }
}