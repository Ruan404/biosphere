<?php

namespace App\Chat;
use App\Core\Database;
use PDO;

class ChatService extends Chat
{
    public function addMessage(Chat $chat): bool
    {
        if (strlen($chat->message) != 0) {
            $query = Database::getPDO()->prepare('INSERT INTO chat(pseudo, message, topic_id) VALUES(?,?,?)');
            $result = $query->execute([$chat->pseudo, $chat->message, $chat->topic_id]);

            return $result;
        }

        return false;
    }

    public function getChatMessages(int $topicId, int $lastMessageId): ?array
    {

        $query = Database::getPDO()->prepare('SELECT * FROM chat WHERE topic_id = :topic AND chat.id > :lastMessageId ORDER BY chat.id ASC LIMIT 50');


        $query->bindParam(':topic', $topicId, PDO::PARAM_STR);
        $query->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);
        $query->execute();

        $messages = $query->fetchAll(PDO::FETCH_CLASS, Chat::class);

        return $messages;
    }
}