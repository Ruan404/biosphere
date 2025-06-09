<?php

namespace App\Message;

use App\Attributes\Route;
use App\Auth\AuthService;
use App\Exceptions\HttpExceptionInterface;
use App\Message\MessageService;
use App\User\UserService;
use Exception;
use function App\Helpers\json;
use function App\Helpers\view;

#[Route("GET", "/message")]
class MessageController
{
    private $messageService;
    private $userService;
    private $authService;

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->userService = new UserService();
        $this->authService = new AuthService();
    }

    #[Route("GET", "")]
    public function index()
    {
        $accept = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
        $recipient = $_GET["user"] ?? "";
        try {

            $users = $this->userService->getUsersExceptOne($_SESSION["user_id"]);

            if ($accept && $recipient) {
                $messages = $this->messageService->getMessagesByUser($recipient);

                return json([
                    "messages" => $messages
                ]);
            }

            return view(view: '/message/index', data: [
                'users' => $users,
                'recipient' => $recipient
            ]);
        } catch (HttpExceptionInterface) {
            header('Location: /message');
            exit();
        } catch (Exception $e) {
            error_log("Message index error: " . $e->getMessage());
            return $accept
                ? json(['messages' => []], 500)
                : view(view: '/message/index', data: ['users' => $users ?? [], 'messages' => []]);
        }
    }

    #[Route("POST", "")]
    public function sendMessage()
    {
        try {
            $recipientUser = $this->userService->getUserByPseudo($_GET["user"]);
            $image = $_FILES["image"] ?? null;
            $text = $_POST['message'] ?? "";

            $newMessage = $this->messageService->sendMessage(
                $image,
                $recipientUser->id,
                $text,
                $_SESSION["user_id"]
            );

            return json(["action" => "add", ...$newMessage]);
        } catch (HttpExceptionInterface $e) {
            return json(["message" => "L'envoi du message a échoué."], $e->getCode());
        } catch (Exception $e) {
            error_log("Send message failed: " . $e->getMessage());
            return json(["message" => "Erreur serveur lors de l'envoi du message."], 500);
        }
    }

    #[Route("DELETE", "")]
    public function deleteMessage()
    {
        try {
            $payload = json_decode(file_get_contents('php://input'), true);
            $messageId = $payload["message"] ?? null;

            $role = $_SESSION["role"];
            $username = $_SESSION["username"];

            $this->messageService->deleteMessage($messageId, $username, $role);

            return json(['success' => true,"action" => "delete", "messages" => [$messageId]]);
        } catch (HttpExceptionInterface $e) {
            return json(['success' => false, 'message' => 'Erreur de suppression.'], $e->getCode());
        } catch (Exception $e) {
            error_log("Delete message failed: " . $e->getMessage());
            return json(['success' => false, 'message' => 'Erreur serveur.'], 500);
        }
    }
}
