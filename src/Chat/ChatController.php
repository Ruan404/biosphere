<?php
namespace App\Chat;


use App\Attributes\Roles;
use App\Attributes\Route;
use App\Auth\AuthService;
use App\Entities\Role;
use App\Topic\TopicService;
use function App\Helpers\view;


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
        return view(view: '/chat/index', data: ['topics' => $this->topics]);
    }

    #[Route("GET", "/api/[*:slug]")]
    public function getChat($params)
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
            $topicId = $topic->id;


            // récupère l'id du dernier message affiché
            $lastMessageId = $_GET['lastMessageId'] ?? 0;
            $messages = new ChatService()->getChatMessages($topicId, $lastMessageId);

            if ($messages == null) {
                header('Location: /chat');
                exit();
            }
            header('Content-Type: application/json');
            echo json_encode(["messages" => $messages]);
        }
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
            return view(view: '/chat/topic', data: ['topics' => $this->topics, 'currentTopic' => $topic->name]);
        }
    }


    #[Route("POST", "/[*:slug]")]
    public function addMessage($params)
    {
        if (!empty($_POST) && isset($params['slug'])) {
            $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
            //topic does not exists
            if ($topic == null) {
                return $this->index();
            }
            $topicId = $topic->id;

            //create a new chat
            $chat = new Chat();
            $chat->message = $_POST['message'];
            $chat->pseudo = $this->authService->getUserSession()->pseudo;
            $chat->topic_id = $topicId;
            $chat->date = date('Y-m-d H:i:s');

            $result = new chatService()->addMessage($chat);
            print_r(json_encode($chat));
            exit();
        }
    }


    #[Route("DELETE", "/[*:slug]")]
    public function deleteMyMessages($params)
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE" && isset($params['slug'])) {
            $rawData = file_get_contents('php://input'); //Be aware that the stream can only be read once

            parse_str($rawData, $data);

            $user = $this->authService::getUserSession();

            if ($user) {
                $topic = new TopicService()->getTopicByName($params["slug"]);

                if ($topic) {
                    return new ChatService()->deleteMyMessages($user->pseudo, $topic->id, [$data["messages"]]);
                }
            }

            return false;
        }
    }
}