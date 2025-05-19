<?php

namespace App\Message;

use App\Attributes\Middleware;
use App\Attributes\Route;
use App\Exceptions\HttpExceptionInterface;
use App\Message\MessageService;
use App\Middleware\IsLoggedInMiddleware;
use App\User\UserService;
use DateTime;
use DateTimeZone;
use Exception;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use function App\Helpers\json;
use function App\Helpers\view;


#[Middleware(new IsLoggedInMiddleware())]
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
    public function index(ServerRequest $request)
    {
        $userParams = $request->getQueryParams()["user"] ?? "";

        // Récupérer la liste des utilisateurs sauf celui connecté
        $users = $this->userService->getUsersExceptOne($_SESSION["user_id"]);

        if ($userParams) {
            $user = $this->userService->getUserByPseudo(htmlspecialchars($userParams));

            if ($user) {
                $messages = $this->messageService->getMessages($user->id);

                return view(view: '/message/index', data: ['messages' => $messages, 'users' => $users, 'recipient' => $user->pseudo]);
            }
        }
        return view(view: '/message/index', data: ['users' => $users, 'messages' => []]);
    }

    // Envoyer un message privé
    #[Route("POST", "")]
    public function sendMessage(ServerRequestInterface $request)
    {
        try {
            $userParams = $request->getQueryParams()["user"];
            
            $user = $this->userService->getUserByPseudo($userParams);
            $payload = $request->getParsedBody();

            // $timezone = new DateTimeZone('Europe/Paris');
            // $date = (new DateTime("now", $timezone))->format('Y-m-d H:i:s');

            $this->messageService->sendMessage($user->id, $payload['message'], $_SESSION["user_id"]);

            return new Response(301, ["location" => "/message?user={$user->pseudo}"]);

        } catch (HttpExceptionInterface $e) {
            return new Response(301, ["location" => "/message?user={$user->pseudo}"]);

        } catch (Exception $e) {
            return new Response(301, ["location" => "/message?user={$user->pseudo}"]);
        }
    }

    // Supprimer un message privé (uniquement pour l'utilisateur ou l'administrateur)
    // Suppression d'un message
    #[Route("DELETE", "")]
    public function deleteMessage($request)
    {
        try {
            $userParams = $request->getQueryParams()["user"] ?? "";

            if ($request->getMethod() !== "DELETE" || !$userParams) {
                return json(['success' => false, 'message' => 'Requête invalide.'], 400);
            }

            $data = json_decode($request->getBody()->getContents(), true);

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