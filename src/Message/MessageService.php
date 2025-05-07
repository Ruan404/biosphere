<?php

namespace App\Message;

use App\Core\Database;
use PDO;

class MessageService
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Récupérer la liste des utilisateurs sauf l'utilisateur connecté
    public function getUsers(): array
    {
        $query = Database::getPDO()->prepare("SELECT id, pseudo FROM users WHERE id != ?");
        $query->execute([$_SESSION['user_id']]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
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
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Envoyer un message
    public function sendMessage(int $recipientId, string $messageContent): bool
    {
        if (strlen(trim($messageContent)) > 0) {
            // Nettoyage du contenu du message
            $messageContent = nl2br(htmlspecialchars($messageContent));

            $query = Database::getPDO()->prepare("
                INSERT INTO messages_privés (message, id_destinataire, id_auteur)
                VALUES (?, ?, ?)
            ");
            $query->execute([$messageContent, $recipientId, $_SESSION['user_id']]);
            return $query->rowCount() > 0;
        }
        return false;
    }

    // Supprimer un message
    public function deleteMessage(int $messageId, string $role): bool
    {
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
    }

    // Récupérer un utilisateur par son ID
    public function getUserById(int $userId): ?array
    {
        $query = Database::getPDO()->prepare("SELECT id, pseudo FROM users WHERE id = ?");
        $query->execute([$userId]);
        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}
?>
