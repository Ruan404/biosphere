<?php

namespace App\Message;

use App\Core\Database;
use App\Message\Dto\GetMessageDto;
use App\Exceptions\BadRequestException;
use Exception;
use PDO;
use PDOException;

class MessageService
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    // Récupérer les messages échangés entre deux utilisateurs
    public function getMessages(int $userId): array
    {
        $query = Database::getPDO()->prepare("
            SELECT mp.*, u.pseudo 
            FROM messages_privés mp
            JOIN users u ON u.id = mp.id_auteur
            WHERE (mp.id_auteur = ? AND mp.id_destinataire = ?)
               OR (mp.id_auteur = ? AND mp.id_destinataire = ?)
            ORDER BY mp.id ASC
        ");
        $query->execute([
            $_SESSION['user_id'],
            $userId,
            $userId,
            $_SESSION['user_id']
        ]);
        return $query->fetchAll(PDO::FETCH_CLASS, GetMessageDto::class);
    }

    // Envoyer un message
    public function sendMessage(int $recipientId, string $messageContent, int $senderId): bool
    {
        try {

            $message = nl2br(htmlspecialchars(trim($messageContent)));

            if (strlen($message > 0)) {
                $query = Database::getPDO()->prepare("
                INSERT INTO messages_privés (message, id_destinataire, id_auteur)
                VALUES (?, ?, ?)
            ");
                $query->execute([$message, $recipientId, $senderId]);

                return true;
            }

            throw new BadRequestException("le message est requis");


        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    // Supprimer un message
    public function deleteMessage(int $messageId, string $role): bool
    {
        try {
            if ($role === 'admin') {
                // Un administrateur peut supprimer n'importe quel message
                $query = Database::getPDO()->prepare("DELETE FROM messages_privés WHERE id = ?");
                $query->execute([$messageId]);

            } else {
                // Un utilisateur normal ne peut supprimer que ses propres messages
                $query = Database::getPDO()->prepare("DELETE FROM messages_privés WHERE id = ? AND id_auteur = ?");
                $query->execute([$messageId, $_SESSION['user_id']]);
            }
            
            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getMessageByDate(string $date): ?Message
    {
        try {
            $query = Database::getPDO()->prepare("SELECT * FROM messages_privés WHERE date = ?");
            $query->setFetchMode(PDO::FETCH_CLASS, Message::class);
            $query->execute([$date]);
            $message = $query->fetch();

            return $message ?: null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}