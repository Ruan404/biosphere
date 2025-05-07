<?php

namespace App\Chat;
use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\Topic\TopicService;
use Exception;
use PDO;
use PDOException;

class ChatService extends Chat
{
    private $topicService;

    public function __construct()
    {
        $this->topicService = new TopicService();
    }
    public function addMessage(Chat $chat, int $topicId): bool
    {
        try{
            if (strlen($chat->message) != 0) {
                $query = Database::getPDO()->prepare('INSERT INTO chat(pseudo, message, topic_id) VALUES(?,?,?)');
                $query->execute([$chat->pseudo, $chat->message, $topicId]);
    
                return true;
            }
    
            throw new BadRequestException("enter a valid message");
            
        }catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getChatMessages(int $topicId, int $lastMessageId): ?array
    {

        try {
            $query = Database::getPDO()->prepare('SELECT chat.pseudo, chat.message, chat.date FROM chat WHERE topic_id = :topic AND chat.id > :lastMessageId ORDER BY chat.id ASC LIMIT 50');


            $query->bindParam(':topic', $topicId, PDO::PARAM_STR);
            $query->bindParam(':lastMessageId', $lastMessageId, PDO::PARAM_INT);
            $query->execute();

            $messages = $query->fetchAll(PDO::FETCH_CLASS, Chat::class);

            return $messages ?: null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    //supprimer tous mes messages
    public function deleteMyMessages(string $pseudo, int $topicId, array $dates): bool
    {
       
        // $in_array = explode(',', $dates[0]);

        $in = str_repeat('?,', count($dates) - 1) . '?';

        $query = Database::getPDO()->prepare("DELETE FROM chat WHERE pseudo=? AND topic_id=? AND date IN ($in)");

        $query->execute(array_merge([$pseudo, $topicId], array_merge($dates)));

        return $query->rowCount() > 0;
    }
    
     public function deleteMessagesAsAdmin(int $topicId, array $dates): bool
     {
        //$in_array = explode(',', $dates[0]);
        $in = str_repeat('?,', count($dates) - 1) . '?';

        $query = Database::getPDO()->prepare("DELETE FROM chat WHERE topic_id=? AND date IN ($in)");
        $query->execute(array_merge([$topicId], $dates));

        return $query->rowCount() > 0;
     }


    public function deleteChat($topicId): string
    {
        try {
            if ($topicId && is_numeric($topicId)) {
                $query = Database::getPDO()->prepare('DELETE FROM chat WHERE topic_id = ?');
                $query->execute([$topicId]);

                return $this->topicService->deleteTopic($topicId);
            }

            throw new BadRequestException("invalid parameter");

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}