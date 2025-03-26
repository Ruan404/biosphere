<?php

namespace App\Admin;

use App\Attributes\Route;
use App\Helpers\Page;
use App\Admin\AdminService;
use App\Film\FilmService;
use App\Topic\TopicService;
use App\User\UserService;

#[Route("GET", "/admin")]
class AdminController {
    private $adminService;

    public function __construct() {
        $this->adminService = new AdminService();
    }

    #[Route("GET", "")]
    public function index() {
        $users = new UserService()->getUsers();
        $topics = new TopicService()->getAllTopics();
        $films = new FilmService()->getAllFilms();

        return Page::print(view: "/admin/index", infos: ['users'=> $users, 'topics'=> $topics, 'films'=> $films]);
    }


    #[Route("POST", "/action")]
    public function handleActions() {
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