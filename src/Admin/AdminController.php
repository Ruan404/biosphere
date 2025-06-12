<?php

namespace App\Admin;

use App\Attributes\Group;
use App\Attributes\Route;
use App\Exceptions\HttpExceptionInterface;
use App\Film\Dto\FilmChunkUploadDto;
use App\Film\FilmService;

use App\Topic\TopicService;
use App\User\UserService;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use function App\Helpers\json;
use function App\Helpers\view;

ini_set('max_execution_time', 300);

#[Group("/admin")]
class AdminController
{
    private AdminService $adminService;
    private FilmService $filmService;
    private UserService $userService;
    private TopicService $topicService;

    public function __construct()
    {
        $this->adminService = new AdminService();
        $this->filmService = new FilmService();
        $this->userService = new UserService();
        $this->topicService = new TopicService();
    }

    #[Route("GET", "/")]
    public function index()
    {
        return view('/admin/index');
    }

    #[Route("GET", "/film/upload")]
    public function upload()
    {
        return view('/admin/filmUpload');
    }

    #[Route("GET", "/{tab}")]
    public function getData($request)
    {
        try {
            $tab = $request->getAttribute("tab");
            error_log("Admin getData hit with tab: " . $tab);

            return match ($tab) {
                "users"  => json($this->userService->adminUsersExceptOne($_SESSION['user_id'] ?? 0)),
                "topics" => json($this->topicService->adminTopics()),
                "films"  => json($this->filmService->adminFilms()),
                default  => json([]),
            };
        } catch (Exception $e) {
            error_log("Admin panel load failed: " . $e->getMessage());
            return json(["success" => false, "message" => "Impossible de charger la page"], 500);
        }
    }

    #[Route("POST", "/film/upload")]
    public function uploadFilm($request)
    {
        $data = $request->getParsedBody();
        $files = $request->getUploadedFiles();
        
        try {
            $dto = new FilmChunkUploadDto(
                $_FILES['file'] ?? [],
                (int) ($_POST['step'] ?? 0),
                (int) ($_POST['totalChunks'] ?? 0),
                $_POST['filename'] ?? '',
                $_POST['token'] ?? '',
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_FILES['cover'] ?? []
            );

            $message = $this->filmService->upload($dto);
            return json(["success" => true, "message" => $message]);

        } catch (Exception $e) {
            error_log("Chunk upload failed: " . $e->getMessage());
            return json(["success" => false, "message" => "Erreur lors du téléchargement"], 500);
        }
    }

    #[Route("POST", "/action")]
    public function handleActions(ServerRequestInterface $request)
    {
        try {
            $action = $_POST['action'] ?? '';
            $slug   = $_POST['slug'] ?? '';
            $slugs  = $_POST['slugs'] ?? [];

            match ($action) {
                'delete_user'     => $this->adminService->deleteUser($slug),
                'delete_users'    => $this->adminService->deleteUsers($slugs),
                'promote_user'    => $this->adminService->promoteUser($slug),
                'delete_topic'    => $this->adminService->deleteTopic($slug),
                'delete_topics'   => $this->adminService->deleteTopics($slugs),
                'add_topic'       => $this->adminService->addTopic($slug),
                'delete_film'     => $this->adminService->deleteFilm($slug),
                'delete_films'    => $this->adminService->deleteFilms($slugs),
                'delete_podcast'  => $this->adminService->deletePodcast($slug),
                default           => json(["success" => false, "message" => "L'action n'a pas pu aboutir"], 400)
            };

            return json(["success" => true, "message" => "Action menée avec succès"]);

        } catch (HttpExceptionInterface $e) {
            return json(["success" => false, "message" => "Erreur logique"], $e->getStatusCode());
        } catch (Exception $e) {
            error_log("Admin action failed: " . $e->getMessage());
            return json(["success" => false, "message" => "L'action n'a pas pu aboutir"], 500);
        }
    }
}
