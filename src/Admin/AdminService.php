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
        throw new NotFoundException("l'utilisateur n'existe pas");
    }

    public function deleteUsers(array $users): bool
    {
        return $this->userService->deleteUsers($users);
    }

    public function promoteUser($pseudo): bool
    {
        $user = $this->userService->getUserByPseudo($pseudo);
        if ($user) {
            return $this->userService->promoteToAdmin($user->id);
        }
        throw new NotFoundException("l'utilisateur n'existe pas");
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
            $this->chatService->deleteChat($topicId);

            return $this->topicService->deleteTopic($topicId);

        }
        throw new NotFoundException("le topic n'existe pas");
    }


    /**
     * Delete chat messages related to a topic and the topic itself
     * @param string $topic
     * @return bool
     */
    public function deleteTopics(array $topicNames)
    {

        $topics = $this->topicService->getTopicsByNames($topicNames);

        $topicsIds = [];


        for ($i = 0; $i < count($topics); $i++) {
            $topicsIds[] = $topics[$i]["id"];
        }

        if ($topicsIds) {

            $this->chatService->deleteChats($topicsIds);
            return $this->topicService->deleteTopics($topicsIds);

        }
        throw new NotFoundException("Aucun topic trouvé");
    }

    public function deletePodcast($podcastTitle)
    {
        $podcast = $this->podcastService->getPodcastByTitle($podcastTitle);

        if ($podcast) {
            $this->podcastService->deletePodcast($podcast->id);
        }
    }

    public function deleteFilm(string|array $token)
    {
        $film = $this->filmService->getFilmByToken($token);

        if ($film === null) {
            throw new NotFoundException("Le film n'existe pas.");
        }

        return $this->filmService->deleteFilm($film);
    }

    public function deleteFilms(array $tokens)
    {
        $films = $this->filmService->getFilmsByTokens($tokens);

        if ($films === null) {
            throw new NotFoundException("Aucun film trouvé.");
        }

        $filmsGrouped = ["files" => [], "token" => []];

        for ($i = 0; $i < count($films); $i++) {
            $filmsGrouped["files"][] = $films[$i]["file_path"];
            $filmsGrouped["files"][] = $films[$i]["cover_image"];
            $filmsGrouped["token"][] = $films[$i]["token"];
        }

        return $this->filmService->deleteFilms($filmsGrouped["files"], $filmsGrouped["token"]);
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
