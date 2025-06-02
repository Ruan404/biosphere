<?php

namespace App\Message;

use App\Attributes\Route;
use App\Exceptions\HttpExceptionInterface;
use App\File\FileService;
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

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->userService = new UserService();
    }

    // Afficher la liste des conversations (utilisateurs sauf l'utilisateur connecté)
    #[Route("GET", "")]
    public function index()
    {
        $accept = strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

        try {
            $user = $_GET["user"] ?? "";
            // Récupérer la liste des utilisateurs sauf celui connecté
            $users = $this->userService->getUsersExceptOne($_SESSION["user_id"]);

            if ($user) {
                if ($accept) {
                    $messages = $this->messageService->getMessagesByUser($user);
                    return json(["messages" => $messages]);
                } else {
                    return view(view: '/message/index', data: ['users' => $users, 'recipient' => $user]);
                }
            } else {
                return view(view: '/message/index', data: ['users' => $users, 'recipient' => $user]);
            }
        } catch (HttpExceptionInterface) {
            header('Location: /message');
            exit();
        } catch (Exception $e) {
            error_log("Something wrong happened: " . $e->getMessage());
            if ($accept) {
                return json(['users' => $users, 'messages' => []], 500);
            } else {
                return view(view: '/message/index', data: ['users' => $users, 'messages' => []]);
            }
        }
    }

    // Envoyer un message privé
    #[Route("POST", "")]
    public function sendMessage()
    {
        try {
            $user = $this->userService->getUserByPseudo($_GET["user"]);

            $image = $_FILES["image"] ?? null;
            $text = $_POST['message'] ?? "";

            $newMessage = $this->messageService->sendMessage($image, $user->id, $text, $_SESSION["user_id"]) ?? [];

            return json(["newMessage" => $newMessage]);

        } catch (HttpExceptionInterface $e) {
            error_log("send message failed: " . $e->getMessage());
            return json(["message" => "l'ajout du message n'a pas pu aboutir"], $e->getCode());

        } catch (Exception $e) {
            error_log("send message failed: " . $e->getMessage());
            return json(["message" => "l'ajout du message n'a pas pu aboutir"], 500);

        }
    }

    // Supprimer un message privé (uniquement pour l'utilisateur ou l'administrateur)
    // Suppression d'un message
    #[Route("DELETE", "")]
    public function deleteMessage()
    {
        try {

            $data = json_decode(file_get_contents('php://input'), true); // Récupère les données envoyées en POST
            $role = $_SESSION["role"];
            $date = $data["message"];

            $this->messageService->deleteMessage($date, $role);

            return json(['success' => true, "action" => "delete", "messages" => [$date]]);

        } catch (HttpExceptionInterface $e) {
            return json(['success' => false, 'message' => 'Échec de la suppression du message. '], $e->getCode());
        } catch (Exception $e) {
            error_log("delete message failed: " . $e->getMessage());
            return json(['success' => false, 'message' => 'Échec de la suppression du message. '], 500);
        }
    }

}