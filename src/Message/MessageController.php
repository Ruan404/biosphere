<?php

namespace App\Message;

use App\Attributes\Route;
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

    public function __construct()
    {
        $this->messageService = new MessageService();
        $this->userService = new UserService();
    }

    // Afficher la liste des conversations (utilisateurs sauf l'utilisateur connecté)
    #[Route("GET", "")]
    public function index()
    {
        $user = htmlspecialchars($_GET["user"] ?? "");

        // Récupérer la liste des utilisateurs sauf celui connecté
        $users = $this->userService->getUsersExceptOne($_SESSION["user_id"]);

        if ($user) {
            $user = $this->userService->getUserByPseudo($user);

            if ($user) {
                $messages = $this->messageService->getMessages($user->id);

                return view(view: '/message/index', data: ['messages' => $messages, 'users' => $users, 'recipient' => [
                    "pseudo" => $user->pseudo,
                    "image" => $user->image
                ]]);
            }
        }
        return view(view: '/message/index', data: ['users' => $users, 'messages' => []]);
    }

    // Envoyer un message privé
    #[Route("POST", "")]
    public function sendMessage()
    {
        try {
            $user = $this->userService->getUserByPseudo(htmlspecialchars($_GET["user"]));

            // $timezone = new DateTimeZone('Europe/Paris');
            // $date = (new DateTime("now", $timezone))->format('Y-m-d H:i:s');

            $this->messageService->sendMessage($user->id, $_POST['message'], $_SESSION["user_id"]);

            header(header: "Location: /message?user={$user->pseudo}");
            exit();
        } catch (HttpExceptionInterface $e) {
            header(header: "Location: /message?user={$user->pseudo}");
            exit();
        } catch (Exception $e) {
            header(header: "Location: /message?user={$user->pseudo}");
            exit();
        }
    }

    // Supprimer un message privé (uniquement pour l'utilisateur ou l'administrateur)
    // Suppression d'un message
    #[Route("DELETE", "")]
    public function deleteMessage()
    {
        try {

            if ($_SERVER['REQUEST_METHOD'] !== "DELETE" && !isset($_GET['user'])) {
                return json(['success' => false, 'message' => 'Requête invalide.'], 400);
            }

            $data = json_decode(file_get_contents('php://input'), true); // Récupère les données envoyées en POST

            $user = $_SESSION["username"] ?? "";
            $role = $_SESSION["role"] ?? "user";
            $date = $data["message"] ?? "";

            if (!$user || !$date) {
                return json(['success' => false, 'message' => 'Données invalides.'], 400);
            }

            $message = $this->messageService->getMessageByDate($date);

            if (!$message) {
                return json(['success' => false, 'message' => 'Message introuvable.'], 404);
            }

            $this->messageService->deleteMessage($message->id, $role);

            return json(['success' => true]);


        } catch (Exception $e) {
            // Gestion des erreurs inattendues
            return json([
                'success' => false,
                'message' => 'Échec de la suppression du message. '
            ], 500);
        }
    }

}
?>