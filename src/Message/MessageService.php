<?php

namespace App\Message;

use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\File\FileService;
use App\User\UserService;
use Exception;
use PDO;
use PDOException;

class MessageService
{
    private $fileService;
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->fileService = new FileService();
    }


    // Récupérer les messages échangés entre deux utilisateurs
    public function getMessagesByUser(string $pseudo): array
    {
        $user = new UserService()->getUserByPseudo($pseudo) ?: throw new NotFoundException("l'utilisateur n'existe pas");


        $query = Database::getPDO()->prepare("
           SELECT 
                mp.date, 
                mp.message, 
                u_sender.pseudo AS sender, 
                u_recipient.pseudo AS recipient
            FROM messages_privés mp
            JOIN users u_sender ON u_sender.id = mp.id_auteur
            JOIN users u_recipient ON u_recipient.id = mp.id_destinataire
            WHERE (mp.id_auteur = ? AND mp.id_destinataire = ?)
            OR (mp.id_auteur = ? AND mp.id_destinataire = ?)
            ORDER BY mp.id ASC;
        ");
        $query->execute([
            $_SESSION['user_id'],
            $user->id,
            $user->id,
            $_SESSION['user_id']
        ]);
        return $query->fetchAll(PDO::FETCH_CLASS, Message::class);
    }

    // Envoyer un message
    public function sendMessage(?array $image, int $recipientId, string $messageContent, int $senderId)
    {
        try {
            if (!$recipientId || !$senderId || empty($messageContent) && !$image) {
                throw new BadRequestException("requête invalide");
            }

            $imageMarkdown = "";
            if ($image) {
                $upload = new FileService();
                $upload->validate(["png", "jpg", "jpeg"], $image["name"]);
                $imagePath = "/" . $upload->generatePath($image["name"], "images/"); //the slash issue must be fixed

                $imageMarkdown = "![alt]({$imagePath})";
            }

            $pdo = Database::getPDO();
            $message = trim("{$imageMarkdown}\n\n{$messageContent}");

            $query = $pdo->prepare("
                INSERT INTO messages_privés (message, id_destinataire, id_auteur)
                VALUES (?, ?, ?)
            ");
            $query->execute([$message, $recipientId, $senderId]);


            $newMessage = $this->getMessageById($pdo->lastInsertId());

            if ($newMessage) {
                if ($image) {
                    $upload->uploadAndSaveFile(
                        $image["tmp_name"],
                        $image["name"],
                        $imagePath,
                        $image["size"],
                        $image["type"],
                        $newMessage->id
                    );
                }
                return ["canDelete" => $newMessage->canDelete, "htmlMessage" => $newMessage->htmlMessage, "isAuthor" => $newMessage->isAuthor, "date" => $newMessage->date, "recipient" => $newMessage->recipient, "sender" => $_SESSION["username"]];

            }

            return null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    // Supprimer un message
    public function deleteMessage(string $date, string $role = "user"): bool
    {
        $message = $this->getMessageByDate($date) ?: throw new NotFoundException("Message introuvable.");
        
        try {
            if ($role === 'admin') {
                // Un administrateur peut supprimer n'importe quel message
                $query = Database::getPDO()->prepare("DELETE FROM messages_privés WHERE id = ?");
                $query->execute([$message->id]);

            } else {
                // Un utilisateur normal ne peut supprimer que ses propres messages
                $query = Database::getPDO()->prepare("DELETE FROM messages_privés WHERE id = ? AND id_auteur = ?");
                $query->execute([$message->id, $_SESSION['user_id']]);
            }

            $image = $this->fileService->getUploadedFileByAuthorId($message->id);

            if ($image) {
                $this->fileService->deleteUploadedFile($image["path"], $message->id);
            }

            return $query->rowCount() > 0 ?: throw new NotFoundException("Aucun message supprimé");

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getMessageByDate(string $date): ?Message
    {
        try {
            $query = Database::getPDO()->prepare("
                SELECT 
                    mp.*, 
                    sender.pseudo AS sender,
                    recipient.pseudo AS recipient
                FROM messages_privés mp
                JOIN users sender ON sender.id = mp.id_auteur
                JOIN users recipient ON recipient.id = mp.id_destinataire
                WHERE mp.date = ?
                ORDER BY mp.id ASC
            ");
            $query->setFetchMode(PDO::FETCH_CLASS, Message::class);
            $query->execute([$date]);
            $message = $query->fetch();

            return $message ?: null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getMessageById(int $id): ?Message
    {
        try {
            $query = Database::getPDO()->prepare("
                SELECT 
                    mp.*, 
                    u_sender.pseudo AS sender, 
                    u_recipient.pseudo AS recipient
                FROM messages_privés mp
                JOIN users u_sender ON u_sender.id = mp.id_auteur
                JOIN users u_recipient ON u_recipient.id = mp.id_destinataire
                WHERE mp.id = ?
                ORDER BY mp.id ASC;
                ");
            $query->setFetchMode(PDO::FETCH_CLASS, Message::class);
            $query->execute([$id]);
            $message = $query->fetch();

            return $message ?: null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}