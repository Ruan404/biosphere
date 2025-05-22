<?php

namespace App\Admin;

use App\Attributes\Middleware;
use App\Attributes\Roles;
use App\Attributes\Route;
use App\Admin\AdminService;
use App\Entities\Layout;
use App\Entities\Role;
use App\Exceptions\HttpExceptionInterface;
use App\Film\FilmService;
use App\Middleware\IsLoggedInMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use function App\Helpers\json;
use App\Topic\TopicService;
use App\User\UserService;
use Exception;
use function App\Helpers\view;
ini_set('max_execution_time', 300);

#[Middleware(new IsLoggedInMiddleware())]
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
        return view(view: "/admin/index", layout: Layout::Admin);
    }


    #[Route("GET", "/admin/film/upload")]
    public function upload()
    {
        return view(view: '/film/upload');
    }

    #[Route("GET", "/admin/{tab}")]
    public function getData($request)
    {
        try {
            $tab = htmlspecialchars($request->getAttribute("tab"));
            switch ($tab) {
                case "users":
                    return json(new UserService()->adminUsersExceptOne($_SESSION['user_id']));

                case "topics":
                    return json(new TopicService()->adminTopics());

                case "films":
                    return json($this->filmService->adminFilms());

                default:
                    return json([]);
            }
        } catch (Exception $e) {
            error_log("Admin panel load failed: " . $e->getMessage());
            return json(["success" => false, "message" => "impossible de charger la page"]);
        }
    }

    // Route for handling the video upload and HLS conversion
    #[Route("POST", "/film/upload")]
    public function uploadFilm($request)
    {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();

        try {
            $token = $data['token'];
            $videoFile = $files['file'];
            $chunkNumber = (int) ($data['step']);
            $totalChunks = (int) ($data['totalChunks']);
            $filename = $data['filename'];
        } catch (Exception) {
            return json(["success"=>false, "message" => "Invalid upload data."], 400);
        }

        try {
            $result = $this->filmService->chunkedUpload($videoFile, $chunkNumber, $totalChunks, $filename, $token);

            if ($result["state"] !== "done") {
                return json(["success"=>false, "message" => "chunk $chunkNumber téléchargé avec success."]);
            } else {
                try {
                    $title = $data['title'];
                    $description = $data['description'];
                    $coverFile = $files['cover'];

                    $cover = $this->filmService->uploadImage($coverFile, $result["token"]);

                    if ($cover) {
                        $this->filmService->addFilm($title, $description, $result["path"], 'playlistPath', $cover, $result["token"]);
                        return json(["success"=>false, "message" => "téléchargement terminé"]);
                    }
                } catch (Exception $e) {
                    error_log("Video upload failed: " . $e->getMessage());
                    return json(["success"=>false, "message" => "une erreur s'est produite lors du téléchargement"]);
                }
            }
        } catch (Exception $e) {
            error_log("Chunk upload failed: " . $e->getMessage());
            return json(["success"=>false, "message" => "une erreur s'est produite lors du téléchargement"]);
        }
    }

    #[Route("POST", "/admin/action")]
    public function handleActions(ServerRequestInterface $request)
    {
        try {
            $data = $request->getParsedBody();
            $action = $data["action"];
            $slug = $data['slug'] ?? "";
            $slugs = $data['slugs'] ?? [];

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
                    $this->adminService->deleteTopic($slug);
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
                    return json(["success" => false, "message" => "l'action n'a pas pu aboutir"]);
            }

            return json(["success" => true, "message" => "action menée avec success"]);
        } catch (HttpExceptionInterface $e) {
            return json(["success" => false, "message" => "l'action n'a pas pu aboutir"], $e->getStatusCode());
        } catch (Exception $e) {
            error_log("Admin action failed: " . $e->getMessage());
            return json(["success" => false, "message" => "l'action n'a pas pu aboutir"], 500);
        }
    }
}