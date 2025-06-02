<?php

namespace App\Chat;
use App\Chat\Dto\AddMessageDto;
use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\File\FileService;
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
    public function addMessage(AddMessageDto $chat)
    {
        try {

            $topic = $this->topicService->getTopicByName($chat->topicName);
            //topic does not exists
            if ($topic === null) {
                throw new BadRequestException("impossible d'ajouter un message");
            }
            $topicId = $topic->id;

            $imageMarkdown = "";
            if ($chat->image) {
                $upload = new FileService();
                $upload->validate(["png", "jpg", "jpeg"], $chat->image["name"]);
                $imagePath = "/" . $upload->generatePath($chat->image["name"], "images/"); //the slash issue must be fixed

                $imageMarkdown = "![alt]({$imagePath})";
            }

            $pdo = Database::getPDO();
            $htmlMessage = trim("{$imageMarkdown}\n\n{$chat->message}");

            if (strlen($htmlMessage) != 0) {
                $query = $pdo->prepare('INSERT INTO chat(pseudo, message, topic_id) VALUES(?,?,?)');
                $query->execute([$chat->pseudo, $htmlMessage, $topicId]);

                $newChat = $this->getChatById($pdo->lastInsertId());

                if ($newChat) {
                    if ($chat->image) {
                        $upload->uploadAndSaveFile(
                            $chat->image["tmp_name"],
                            $chat->image["name"],
                            $imagePath,
                            $chat->image["size"],
                            $chat->image["type"],
                            $newChat->id
                        );
                    }

                    return ["pseudo" => $newChat->pseudo, "date" => $newChat->date, "options" => $newChat->options, "htmlMessage" => $newChat->htmlMessage, 'topic' => $topic->name];

                }

                return [];
            }

            throw new BadRequestException("enter a valid message");

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getChatMessages(string $topicName): ?array
    {

        try {

            $topic = $this->topicService->getTopicByName($topicName);
            //topic does not exists
            if ($topic === null) {
                throw new NotFoundException("le topic n'existe pas");
            }
            
            $topicId = $topic->id;

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

    public function handleDeletion(string $topicName, array $data, ?string $username, string $role = 'user'): array
    {
        $topic = $this->topicService->getTopicByName($topicName);
        if (!$topic) {
            throw new NotFoundException("Le topic n'existe pas");
        }

        // 1. Cast messages to chat objects
        $chats = (array) $this->getChatsByDates($data['messages']);

        // 2. Extract chat IDs
        $chatIds = [];
        for ($i = 0; $i < count($chats); $i++) {
            $chatIds[] = $chats[$i]["id"];

        }

        if (empty($chatIds)) {
            throw new NotFoundException("Aucun message trouvé");
        }

        // 3. Delete from DB
        $success = ($role === 'admin')
            ? $this->deleteMessagesAsAdmin($topic->id, $chatIds)
            : $this->deleteMyMessages($username, $topic->id, $chatIds);

        if (!$success) {
            throw new BadRequestException('Aucun message supprimé');
        }

        // 4. Delete associated files
        $fileService = new FileService();
        foreach ($chatIds as $id) {
            $image = $fileService->getUploadedFileByAuthorId($id);
            if ($image) {
                $fileService->deleteUploadedFile($image['path'], $id);
            }
        }

        return [
            'success' => 'Deletion succeeded',
            'action' => 'delete',
            'messages' => $data["messages"],
        ];
    }



    //supprimer tous mes messages
    private function deleteMyMessages(string $pseudo, int $topicId, array $ids): bool
    {
        try {
            $in = str_repeat('?,', count($ids) - 1) . '?';

            $query = Database::getPDO()->prepare("DELETE FROM chat WHERE pseudo=? AND topic_id=? AND id IN ($in)");
            $query->execute(array_merge([$pseudo, $topicId], $ids));

            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    private function deleteMessagesAsAdmin(int $topicId, array $ids): bool
    {
        try {
            $in = str_repeat('?,', count($ids) - 1) . '?';

            $query = Database::getPDO()->prepare("DELETE FROM chat WHERE topic_id=? AND id IN ($in)");
            $query->execute(array_merge([$topicId], $ids));

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

            $query = Database::getPDO()->prepare('SELECT * FROM chat WHERE id = ?');
            $query->setFetchMode(PDO::FETCH_CLASS, Chat::class);
            $query->execute([$id]);

            $chat = $query->fetch();

            return $chat ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }


    public function getChatsByDates($dates)
    {
        try {

            $in = str_repeat('?,', count($dates) - 1) . '?';


            $query = Database::getPDO()->prepare("SELECT * FROM chat WHERE date IN ($in)");
            $query->execute($dates);

            $chats = $query->fetchAll(PDO::FETCH_ASSOC);

            return $chats ?: null;

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