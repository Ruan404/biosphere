<?php

namespace App\Admin;

use App\Attributes\Middleware;
use App\Attributes\Roles;
use App\Attributes\Route;
use App\Admin\AdminService;
use App\Entities\Layout;
use App\Entities\Role;
use App\Film\FilmService;
use App\Middleware\IsLoggedInMiddleware;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use function App\Helpers\json;
use App\Topic\TopicService;
use App\User\UserService;
use Exception;
use function App\Helpers\view;
ini_set('max_execution_time', 300);

#[Middleware(new IsLoggedInMiddleware())]
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
    public function uploadFilm(ServerRequestInterface $request)
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
            return json(["error" => "Invalid upload data."], 400);
        }

        try {
            $result = $this->filmService->chunkedUpload($videoFile, $chunkNumber, $totalChunks, $filename, $token);

            if ($result["state"] !== "done") {
                return json(["message" => "chunk $chunkNumber téléchargé avec success."]);
            } else {
                try {
                    $title = $data['title'];
                    $description = $data['description'];
                    $coverFile = $files['cover'];

                    $cover = $this->filmService->uploadImage($coverFile, $result["token"]);

                    if ($cover) {
                        $this->filmService->addFilm($title, $description, $result["path"], 'playlistPath', $cover, $result["token"]);
                        return json(["message" => "téléchargement terminé"]);
                    }
                } catch (Exception $e) {
                    error_log("Video upload failed: " . $e->getMessage());
                    return json(["error" => "une erreur s'est produite lors du téléchargement"]);
                }
            }
        } catch (Exception $e) {
            error_log("Chunk upload failed: " . $e->getMessage());
            return json(["error" => "une erreur s'est produite lors du téléchargement"]);
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
                        $this->adminService->addTopic($topic);  // Appeler la méthode addTopic
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

            return new Response(status: 200,headers: ["location" => "/admin"]);
        }
    }
}