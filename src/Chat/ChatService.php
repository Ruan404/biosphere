<?php

namespace App\Chat;
use App\Chat\Dto\CreateChatDto;
use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\Topic\TopicService;
use Exception;
use PDO;
use PDOException;

class ChatService
{
    private $topicService;

    public function __construct()
    {
        $this->topicService = new TopicService();
    }
    public function addMessage(CreateChatDto $chat, int $topicId)
    {
        try {
            $pdo = Database::getPDO();
            if (strlen($chat->message) != 0) {
                $query = $pdo->prepare('INSERT INTO chat(pseudo, message, topic_id) VALUES(?,?,?)');
                $query->execute([$chat->pseudo, $chat->message, $topicId]);

                return $this->getChatById($pdo->lastInsertId());
            }

            throw new BadRequestException("enter a valid message");

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getChatMessages(int $topicId): ?array
    {

        try {
            $query = Database::getPDO()->prepare('SELECT chat.pseudo, chat.message, chat.date FROM chat WHERE topic_id = :topic ORDER BY chat.id ASC LIMIT 50');

            $query->bindParam(':topic', $topicId, PDO::PARAM_STR);
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
        try {
            $in = str_repeat('?,', count($dates) - 1) . '?';

            $query = Database::getPDO()->prepare("DELETE FROM chat WHERE pseudo=? AND topic_id=? AND date IN ($in)");
            $query->execute(array_merge([$pseudo, $topicId], $dates));

            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function deleteMessagesAsAdmin(int $topicId, array $dates): bool
    {
        try {
            $in = str_repeat('?,', count($dates) - 1) . '?';

            $query = Database::getPDO()->prepare("DELETE FROM chat WHERE topic_id=? AND date IN ($in)");
            $query->execute(array_merge([$topicId], $dates));

            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }


    public function deleteChat($topicId): bool
    {
        try {
            if ($topicId && is_numeric($topicId)) {
                $query = Database::getPDO()->prepare('DELETE FROM chat WHERE topic_id = ?');
                $query->execute([$topicId]);

                return $query->rowCount() > 0;
            }

            throw new BadRequestException("invalid parameter");

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }


    public function getChatById($id): ?Chat
    {
        try {
           
            $query = Database::getPDO()->prepare('SELECT pseudo, message, date FROM chat WHERE id = ?');
            $query->setFetchMode(PDO::FETCH_CLASS, Chat::class);
            $query->execute([$id]);
            
            $chat = $query->fetch();
          
            return $chat ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function deleteChats($topicsIds): bool
    {
        try {
            $in = str_repeat('?,', count($topicsIds) - 1) . '?';

            $query = Database::getPDO()->prepare("DELETE FROM chat WHERE topic_id IN ($in)");
            $query->execute($topicsIds);

            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}