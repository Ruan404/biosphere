<?php

namespace App\Message;

use App\Attributes\Route;
use App\Auth\AuthService;
use App\Message\MessageService;
use DateTime;
use DateTimeZone;
use function App\Helpers\view;

#[Route("GET", "/message")]
class MessageController
{
    private $authService;
    private $messageService;

    public function __construct()
    {
        $this->authService = new AuthService();
        $this->messageService = new MessageService();
    }

    // Afficher la liste des conversations (utilisateurs sauf l'utilisateur connecté)
    #[Route("GET", "")]
    public function index()
    {
        // Récupérer la liste des utilisateurs sauf celui connecté
        $users = $this->messageService->getUsers();

        return view(view: '/message/index', data: ['users' => $users]);
    }

    // Afficher la conversation avec un autre utilisateur
    #[Route("GET", "/[*:user_id]")]
    public function viewMessages($params)
    {
        if (isset($params['user_id'])) {
            $userId = (int) $params['user_id'];
            $messages = $this->messageService->getMessages($userId);
            $user = $this->messageService->getUserById($userId);
            return view(view: '/message/index', data: ['messages' => $messages, 'user' => $user]);
        }
        return $this->index(); // Si aucun user_id n'est spécifié, retourner à la liste des utilisateurs
    }


    // Envoyer un message privé
    #[Route("POST", "/[*:user_id]")]
    public function sendMessage($params)
    {
        if (!empty($_POST) && isset($params['user_id'])) {
            $recipientId = (int) $params['user_id'];
            $messageContent = $_POST['message'];
    
            if (!empty($messageContent)) {
                $timezone = new DateTimeZone('Europe/Paris');
                $date = new DateTime("now", $timezone)->format('Y-m-d H:i:s');
                $user = $_SESSION['username'] ?? null;
    
                if ($user) {
                    // Appeler la méthode pour envoyer le message
                    $result = $this->messageService->sendMessage($recipientId, $messageContent);
    
                    if ($result) {
                        // Rediriger vers la conversation avec l'utilisateur
                       header("Location: /message?user_id={$recipientId}");
                        exit(); // Assurez-vous d'arrêter l'exécution du code ici
                    }
                }
            }
        }
    
        return $this->viewMessages($params); // En cas d'échec, revenir à la conversation
    }

    // Supprimer un message privé (uniquement pour l'utilisateur ou l'administrateur)
    // Suppression d'un message
    #[Route("DELETE", "/[*:user_id]")]
    public function deleteMessage($params)
    {
        if ($_SERVER['REQUEST_METHOD'] === "DELETE" && isset($params['user_id'])) {
            $data = json_decode(file_get_contents('php://input'), true); // Récupère les données envoyées en POST

            $user = $_SESSION["username"] ?? null;
            $role = $_SESSION["role"] ?? "user";

            if ($user && isset($data['message_id'])) {
                $messageId = (int) $data['message_id'];

                // Appeler la méthode de suppression du message dans MessageService
                $response = $this->messageService->deleteMessage($messageId, $role);

                if ($response) {
                    // Si suppression réussie, renvoyer un message de succès
                    $recipientId = (int) $params['user_id']; // ID du destinataire
                    $messages = $this->messageService->getMessages($recipientId);

                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'success', 'messages' => $messages]);
                    exit();
                } else {
                    // Si suppression échoue, renvoyer un message d'erreur
                    header('Content-Type: application/json');
                    echo json_encode(['status' => 'error', 'message' => 'Échec de la suppression du message.']);
                    exit();
                }
            } else {
                // Si l'utilisateur ou l'ID du message est invalide
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Données invalides.']);
                exit();
            }
        }
    }
}
?>