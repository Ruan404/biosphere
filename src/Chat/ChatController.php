<?php
namespace App\Chat;


use App\Attributes\Group;
use App\Attributes\Route;
use App\Auth\AuthService;
use App\Chat\Dto\AddMessageDto;
use App\Entities\Layout;
use App\Exceptions\HttpExceptionInterface;
use App\Topic\TopicService;
use App\Chat\ChatService;
use Exception;
use function App\Helpers\json;
use function App\Helpers\view;


#[Group("/chat")]
class ChatController
{
    private $topics;
    private $chatService;
    private $topicService;
    private $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->chatService = new chatService();
        $this->topicService = new TopicService();
    }

    #[Route("GET", "/")]
    public function index()
    {
        $topics = $this->topicService->getAllTopics();
        return view(view: '/chat/index', data: ['topics' => $topics]);
    }
    

    #[Route("GET", "/{slug}")]
    public function viewChat($request)
    {
        $accept = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        try {
            if ($accept) {
                $username = $_SESSION['username'] ?? null;
                $role = $_SESSION['role'] ?? 'guest';

                // Fetch messages
                $messages = $this->chatService->getChatMessages($request->getAttribute("slug"));
               
                $permissions = $this->authService->getPermissions($role, "chat");
                
                return json([
                    "messages" => $messages,
                    "permissions" => $permissions,
                    "currentUser" => $username
                ]);
            } else {
                $topics = $this->topicService->getAllTopics();
                $topic = $this->topicService->getTopicByName($request->getAttribute("slug"));

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


    #[Route("DELETE", "/{slug}")]
    public function deleteMyMessages($request)
    {
        try {
            $username = $_SESSION['username'] ?? null;
            $role = $_SESSION['role'];
            $data = json_decode(file_get_contents('php://input'), true);

            $result = $this->chatService->handleDeletion($request->getAttribute("slug"), $data, $username, $role);

            return json($result);

        } catch (HttpExceptionInterface $e) {
            return json(["error" => $e->getMessage()], $e->getCode());

        } catch (Exception $e) {
            error_log("Unexpected error: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }



    #[Route("POST", "/{slug}")]
    public function addMessage($request)
    {
        $slug = $request->getAttribute('slug');

        $data = $request->getParsedBody();


        try {
            $image = $_FILES["image"] ?? null;

            $addChat = new AddMessageDto($_SESSION["username"], $_POST["message"], $slug, $image);
            
            $newChat = $this->chatService->addMessage($addChat);
           
            return json(["action" => "add", ...$newChat]);


        } catch (HttpExceptionInterface $e) {
            return json(["error" => $e->getMessage()], $e->getCode());

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }
}