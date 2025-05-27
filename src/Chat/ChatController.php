<?php
namespace App\Chat;


use App\Attributes\Route;
use App\Auth\AuthService;
use App\Chat\Dto\AddMessageDto;
use App\Entities\Layout;
use App\Exceptions\BadRequestException;
use App\Exceptions\HttpExceptionInterface;
use App\Helpers\Response;
use App\Topic\TopicService;
use App\Chat\ChatService;
use DateTime;
use DateTimeZone;
use Exception;
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
        try {
            if (isset($params['slug'])) {
                $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
                //topic does not exists
                if ($topic == null) {
                    header('Location: /chat');
                    exit();
                }
                $topicId = $topic->id;

                // récupère l'id du dernier message affiché
                $messages = new ChatService()->getChatMessages($topicId);

                return new Response()->json(["messages" => $messages]);
            }
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }


    #[Route("GET", "/[*:slug]")]
    public function viewChat($params)
    {
        try {
            if (isset($params['slug'])) {
                $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
                //topic does not exists
                if ($topic === null) {
                    header('Location: /chat');
                    exit();
                }
                return view(view: '/chat/topic', data: ['topics' => $this->topics, 'currentTopic' => $topic->name]);
            }
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
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
            $role = $_SESSION['role'] ?? 'user';
            $data = json_decode(file_get_contents('php://input'), true);

            $result = (new ChatService())->handleDeletion($params["slug"],$data,$username,$role);

            return (new Response())->json($result);
            
        } catch (HttpExceptionInterface $e) {
            return new Response()->json(["error" => $e->getMessage()], $e->getCode());

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

            $chat = new AddMessageDto($_SESSION["username"], $_POST["message"], htmlspecialchars($params['slug']), $image);

            $newChat = new chatService()->addMessage($chat);

            return new Response()->json($newChat);


        } catch (HttpExceptionInterface $e) {
            return new Response()->json(["error" => $e->getMessage()], $e->getCode());

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }
}