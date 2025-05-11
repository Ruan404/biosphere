<?php
namespace App\Chat;


use App\Attributes\Middleware;
use App\Attributes\Route;
use App\Auth\AuthService;
use App\Entities\Layout;
use App\Exceptions\BadRequestException;
use App\Middleware\IsLoggedInMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use function App\Helpers\json;
use App\Topic\TopicService;
use App\Chat\ChatService;
use DateTime;
use DateTimeZone;
use Exception;
use function App\Helpers\view;

#[Middleware(new IsLoggedInMiddleware())]
#[Route("GET", "/chat")]
class ChatController
{
    private $topics;

    private $chatService;

    public function __construct()
    {
        $this->topics = new TopicService()->getAllTopics();
        $this->chatService = new ChatService();
    }

    #[Route("GET", "")]
    public function index()
    {
        return view(view: '/chat/index', data: ['topics' => $this->topics]);
    }

    #[Route("GET", "/api/[*:slug]")]
    public function getChat($request)
    {
        $params = $request->getAttribute('params');
        try {
            if (isset($params['slug'])) {
                $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
                //topic does not exists
                if ($topic == null) {
                    return json(["success"=>false, "message"=>"le topic n'existe pas"], 404);
                }

                $topicId = $topic->id;


                $messages = $this->chatService->getChatMessages($topicId);

                return json(["messages" => $messages]);
            }
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }


    #[Route("GET", "/[*:slug]")]
    public function viewChat($request)
    {
        $params = $request->getAttribute('params');

        try {
            if (isset($params['slug'])) {
                $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
                //topic does not exists
                if ($topic === null) {
                    return view("/errors/404", Layout::Error);
                }
                return view(view: '/chat/topic', data: ['topics' => $this->topics, 'currentTopic' => $topic->name]);
            }
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }

    #[Route("DELETE", "/[*:slug]")]
    public function deleteMyMessages(ServerRequestInterface $request)
    {
        $params = $request->getAttribute('params');

        try {
            if ($request->getMethod() === "DELETE" && isset($params['slug'])) {
                $data = json_decode($request->getBody()->getContents(), true);

                $user = $_SESSION["username"];
                $role = $_SESSION["role"];

                if ($user) {
                    $topic = new TopicService()->getTopicByName($params["slug"]);


                    if ($topic) {
                        if ($role === "admin") {
                            $response = $this->chatService->deleteMessagesAsAdmin($topic->id, [$data["messages"]]);
                        } else {
                            // Sinon, ne supprime que ses propres messages
                            $response = $this->chatService->deleteMyMessages($user, $topic->id, [$data["messages"]]);
                        }

                        if ($response) {
                            return json(["success" => "deletion succeeded", "action" => "delete", ...$data]);
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
    public function addMessage($request)
    {
        $params = $request->getAttribute('params');

        $data = $request->getParsedBody();


        try {
            if (!empty($data) && isset($params['slug'])) {
                $topic = new TopicService()->getTopicByName(htmlspecialchars($params['slug']));
                //topic does not exists
                if ($topic == null) {
                    return json(["success"=>false, "message"=>"le topic n'existe pas"], 404);
                }

                $timezone = new DateTimeZone('Europe/Paris');
                $date = new DateTime("now", $timezone)->format('Y-m-d H:i:s');

                $chat = new Chat($_SESSION["username"], $date);
                $chat->message = $data['message'];

                $this->chatService->addMessage($chat, $topic->id);

                return json([...$chat, 'topic' => $topic->name]);

            }
            return json(["error" => "enter required fields"], 400);
        } catch (BadRequestException $e) {
            return json(["error" => $e->getMessage()], $e->getCode());

        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            return view("/errors/500", Layout::Error);
        }
    }
}