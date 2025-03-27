<?php
namespace App\Admin;

use App\Topic\TopicService;
use App\User\UserService;
use App\Film\FilmService;
use App\Podcast\PodcastService;

class AdminService {

    private $userService;
    private $filmService;
    private $podcastService;
    private $topicService;

    public function __construct(){
        $this->userService = new UserService();
        $this->topicService = new TopicService();
        $this->filmService = new FilmService();
        $this->podcastService = new PodcastService();
    }

    public function deleteUser($pseudo) {
        // Assuming you have a method to find a user by pseudo
        $user = $this->userService->getUserByPseudo($pseudo);
        if ($user) {
            $this->userService->deleteUser($user->id);
            // Add further logic or notifications after deletion
        }
    }

    public function promoteUser($pseudo) {
        $user = $this->userService->getUserByPseudo($pseudo);
        if ($user) {
            $this->userService->promoteToAdmin($user->id);
            // Add further logic for user promotion
        }
    }

    public function deleteTopic($topicName) {
        $topic = $this->topicService->getTopicByName($topicName);
        if ($topic) {
            $this->topicService->deleteTopic($topic->id);
        }
    }

    public function deletePodcast($podcastTitle) {
        $podcast =  $this->podcastService->getPodcastByTitle($podcastTitle);

        if ($podcast) {
            $this->podcastService->deletePodcast($podcast->id);
        }
    }

    public function deleteFilm($filmTitle) {
        $this->filmService->deleteFilm($filmTitle);

    }
}
