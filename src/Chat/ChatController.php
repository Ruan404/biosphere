<?php
namespace App\Chat;


use App\Attributes\Route;
use App\Auth\AuthService;
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
                $lastMessageId = $_GET['lastMessageId'] ?? 0;
                $messages = new ChatService()->getChatMessages($topicId, $lastMessageId);
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
    public function deleteMyMessages($params)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === "DELETE" && isset($params['slug'])) {
                $data = json_decode(file_get_contents('php://input'), true);

                if (session_status() === 1) {
                    session_start();
                }
                $user = $_SESSION["username"];

                if ($user) {
                    $topic = new TopicService()->getTopicByName($params["slug"]);

                    if ($topic) {
                        $response = new ChatService()->deleteMyMessages($user, $topic->id, [$data["messages"]]);
                        if ($response) {
                            return new Response()->json(["success" => "deletion succeeded", "action" => "delete",...$data]);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }


    #[Route("POST", "/[*:slug]")]
    public function addMessage($params)
    {
        try {
            if (!empty($_POST) && isset($params['slug'])) {
                $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
                //topic does not exists
                if ($topic == null) {
                    return $this->index();
                }
                $topicId = $topic->id;

                //create a new chat
                if (session_status() === 1) {
                    session_start();
                }
                $timezone = new DateTimeZone('Europe/Paris');
                $date = new DateTime("now", $timezone)->format('Y-m-d H:i:s');

                $chat = new Chat($_SESSION["username"], $date);
                $chat->message = $_POST['message'];

                $result = new chatService()->addMessage($chat, $topicId);

                return new Response()->json($chat);

            }
            return new Response()->json(["error" => "enter required fields"], 400);
        } catch (BadRequestException $e) {
            return new Response()->json(["error" => $e->getMessage()], $e->getCode());

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }
}