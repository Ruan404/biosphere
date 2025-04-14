<?php
namespace App\Admin;

use App\Chat\ChatService;
use App\Topic\TopicService;
use App\User\UserService;
use App\Film\FilmService;
use App\Podcast\PodcastService;

class AdminService {

    private $userService;
    private $filmService;
    private $podcastService;
    private $topicService;
    private $chatService;

    public function __construct(){
        $this->userService = new UserService();
        $this->topicService = new TopicService();
        $this->chatService = new ChatService();
        $this->filmService = new FilmService();
        $this->podcastService = new PodcastService();
    }

    public function deleteUser($pseudo): bool {
        $user = $this->userService->getUserByPseudo($pseudo);
        if ($user) {
            return $this->userService->deleteUser($user->id);
        }
        return false;
    }

    public function promoteUser($pseudo): bool {
        $user = $this->userService->getUserByPseudo($pseudo);
        if ($user) {
            return $this->userService->promoteToAdmin($user->id);
        }
        return false;
    }
    
    /**
     * Delete chat messages related to a topic and the topic itself
     * @param string $topic
     * @return bool
     */
    public function deleteTopic(string $topic): bool
    {
        $topicId = $this->topicService->getTopicByName($topic)->id;

        if ($topicId) {
            $deleteChat = $this->chatService->deleteChat($topicId);

            if($deleteChat){
                return $this->topicService->deleteTopic($topicId);
            }
            return false;
        }

        return false;
    }

    public function deletePodcast($podcastTitle) {
        $podcast =  $this->podcastService->getPodcastByTitle($podcastTitle);

        if ($podcast) {
            $this->podcastService->deletePodcast($podcast->id);
        }
    }

    public function deleteFilm($token) {
        return $this->filmService->deleteFilm(token: $token);
    }

    public function addTopic($name) {
        $existingTopic = $this->topicService->getTopicByName($name);
        
        if ($existingTopic) {
            return "Le topic existe déjà.";
        } else {
            $success = $this->topicService->addTopic($name);
            return $success ? "Le topic a été ajouté avec succès." : "Erreur lors de l'ajout du topic.";
        }
    }
}
