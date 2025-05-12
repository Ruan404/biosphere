<?php
namespace App\Admin;

use App\Chat\ChatService;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\Topic\TopicService;
use App\User\UserService;
use App\Film\FilmService;
use App\Podcast\PodcastService;

class AdminService
{

    private $userService;
    private $filmService;
    private $podcastService;
    private $topicService;
    private $chatService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->topicService = new TopicService();
        $this->chatService = new ChatService();
        $this->filmService = new FilmService();
        $this->podcastService = new PodcastService();
    }

    public function deleteUser($pseudo): bool
    {
        $user = $this->userService->getUserByPseudo($pseudo);
        if ($user) {
            return $this->userService->deleteUser($user->id);
        }
        throw new BadRequestException("l'utilisateur n'existe pas");
    }

    public function promoteUser($pseudo): bool
    {
        $user = $this->userService->getUserByPseudo($pseudo);
        if ($user) {
            return $this->userService->promoteToAdmin($user->id);
        }
        throw new BadRequestException("l'utilisateur n'existe pas");
    }

    /**
     * Delete chat messages related to a topic and the topic itself
     * @param string $topic
     * @return bool
     */
    public function deleteTopic(string $topic)
    {
        $topicId = $this->topicService->getTopicByName($topic)->id;

        if ($topicId) {
            $deleteChat = $this->chatService->deleteChat($topicId);

            if ($deleteChat) {
                return $this->topicService->deleteTopic($topicId);
            }
        }
        throw new BadRequestException("le topic n'existe pas");
    }

    public function deletePodcast($podcastTitle)
    {
        $podcast = $this->podcastService->getPodcastByTitle($podcastTitle);

        if ($podcast) {
            $this->podcastService->deletePodcast($podcast->id);
        }
    }

    public function deleteFilm($token)
    {
        $film = $this->filmService->getFilmByToken($token);

        if ($film === null) {
            throw new BadRequestException("Le film n'existe déjà.");
        }

        return $this->filmService->deleteFilm(video: $film);
    }

    public function addTopic($name)
    {
        $existTopic = $this->topicService->getTopicByName($name);

        if ($existTopic) {
            throw new BadRequestException("Le topic existe déjà.");

        } else {
            return $this->topicService->addTopic($name);
        }
    }
}
