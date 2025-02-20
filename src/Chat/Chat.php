<?php

namespace App\Chat;

use PDO;
use App\Database\Database;


//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Chat
{
    public int $topic_id {
        get => $this->topic_id;

        set(int $topic_id) {
            $this->topic_id = $topic_id;
        }
    }


    public string $pseudo {
        get => htmlspecialchars($this->pseudo);

        set(string $pseudo) {
            $this->pseudo = $pseudo;
        }
    }

    public string $date {
        get => $this->date;
    }

    public string $message {

        //nl2br permet à l'utilisateur de sauter des lignes
        get => nl2br(rtrim(strip_tags(htmlspecialchars($this->message))));

        set(string $message) {
            $this->message = trim($message);
        }
    }

    public function addMessage(Chat $chat): bool
    {
        if (strlen($this->message) != 0) {
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