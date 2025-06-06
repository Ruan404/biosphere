<?php
namespace App\Chat;


use App\Attributes\Route;
use App\Auth\AuthService;
use App\Chat\Dto\AddMessageDto;
use App\Entities\Layout;
use App\Exceptions\HttpExceptionInterface;
use App\Topic\TopicService;
use App\Chat\ChatService;
use App\User\UserService;
use Exception;
use function App\Helpers\json;
use function App\Helpers\view;


#[Route("GET", "/chat")]
class ChatController
{
    private $authService;
    private $topics;
    private $chatService;
    private $topicService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->chatService = new chatService();
        $this->topicService = new TopicService();
    }

    #[Route("GET", "")]
    public function index()
    {
        $topics = $this->topicService->getAllTopics();
        return view(view: '/chat/index', data: ['topics' => $topics]);
    }

    #[Route("GET", "/actions/[*:slug]")]
    public function actions($params)
    {
        $pseudo = $params["slug"];
        
        $userActions = new UserService()->getUserActions($pseudo);
        return json([$userActions]);
    }

    #[Route("GET", "/[*:slug]")]
    public function viewChat($params)
    {
        $accept = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        try {
            if ($accept) {
                $messages = $this->chatService->getChatMessages($params['slug']);
                return json(["messages" => $messages]);

            } else {
                $topics = $this->topicService->getAllTopics();
                $topic = $this->topicService->getTopicByName($params['slug']);

                if ($topic === null) {
                    header('Location: /chat');
                    exit();
                }

                return view(view: '/chat/index', data: ['topics' => $topics, 'currentTopic' => $topic->name]);
            }

        } catch (HttpExceptionInterface) {
            header('Location: /chat');
            exit();
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());

            if ($accept) {
                return json(["success" => false, "message" => "impossible de recupérer les messages"]);
            } else {
                return view("/errors/500", Layout::Error);
            }
        }
    }

    #[Route("DELETE", "/[*:slug]")]
    public function deleteMyMessages(array $params)
    {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $username = $_SESSION['username'] ?? null;
            $role = $_SESSION['role'];
            $data = json_decode(file_get_contents('php://input'), true);

            $result = $this->chatService->handleDeletion($params["slug"], $data, $username, $role);
            
            return json($result);

        } catch (HttpExceptionInterface $e) {
            return json(["error" => $e->getMessage()], $e->getCode());

        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }



    #[Route("POST", "/[*:slug]")]
    public function addMessage($params)
    {
        try {
            //create a new chat
            if (session_status() === 1) {
                session_start();
            }

            $image = $_FILES["image"] ?? null;

            $chat = new AddMessageDto($_SESSION["username"], $_POST["message"], $params['slug'], $image);
            $newChat = $this->chatService->addMessage($chat);

            return json($newChat);


        } catch (HttpExceptionInterface $e) {
            return json(["error" => $e->getMessage()], $e->getCode());

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }
}