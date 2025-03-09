<?php
namespace App\Chat;

use App\Attributes\Route;
use App\Auth\AuthService;
use App\Helpers\Page;
use App\Topic\TopicService;


#[Route("GET", "/chat")]
class ChatController
{
    private $authService;
    private $topics;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->topics = new TopicService()->getAllTopics();
    }

    #[Route("GET", "")]
    public function index()
    {
        return Page::print(view: '/chat/index', infos: ['topics' => $this->topics]);
    }

    #[Route("GET", "/[*:slug]")]
    public function viewChat($params)
    {
        /**
         * récupération des messages du chat en fonction du topic passé en url
         * 
         */

        if (isset($params['slug'])) {
            $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
            //topic does not exists
            if ($topic == null) {
                header('Location: /chat');
                exit();
            }
 
            return Page::print(view: '/chat/index', infos: ['topics' => $this->topics, 'currentTopic' => $topic->name]);

        }
    }
}