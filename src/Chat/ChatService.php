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
        try {
            if (strlen($chat->message) != 0) {
                $query = Database::getPDO()->prepare('INSERT INTO chat(pseudo, message, topic_id) VALUES(?,?,?)');
                $query->execute([$chat->pseudo, $chat->message, $topicId]);

                return true;
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
            $query = Database::getPDO()->prepare(
                'SELECT chat.pseudo, chat.message, chat.date, users.image 
                FROM chat JOIN users ON users.pseudo = chat.pseudo 
                WHERE chat.topic_id = :topic 
                ORDER BY chat.id ASC 
                LIMIT 50'
            );

            $query->bindParam(':topic', $topicId, PDO::PARAM_INT);
            $query->execute();

            $messages = $query->fetchAll(PDO::FETCH_ASSOC);

           foreach ($messages as &$msg) {
                if (!empty($msg['image'])) {
                    $filename = basename($msg['image']);
                    $avatarPath = "/uploads/images/avatars/" . $filename;
                    $fullPath = $_SERVER["DOCUMENT_ROOT"] . $avatarPath;
                    $timestamp = file_exists($fullPath) ? filemtime($fullPath) : time();
                    $msg['image'] = $avatarPath . '?v=' . $timestamp;
                } else {
                    $firstLetter = strtoupper(substr($msg['pseudo'], 0, 1));
                    $avatarPath = "/uploads/images/avatars/{$firstLetter}.png";
                    $fullPath = $_SERVER["DOCUMENT_ROOT"] . $avatarPath;
                    $timestamp = file_exists($fullPath) ? filemtime($fullPath) : time();
                    $msg['image'] = $avatarPath . '?v=' . $timestamp;
                }
            }

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

        $query->execute(array_merge([$pseudo, $topicId], $dates));

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


    public function deleteChat($topicId): bool
    {
        try {
            if ($topicId && is_numeric($topicId)) {
                $query = Database::getPDO()->prepare('DELETE FROM chat WHERE topic_id = ?');
                $query->execute([$topicId]);

                return true;
            }

            throw new BadRequestException("invalid parameter");

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