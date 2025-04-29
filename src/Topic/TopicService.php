<?php
namespace App\Topic;
use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use Exception;
use PDO;
use PDOException;

class TopicService
{
    public function getTopicByName($name): ?Topic
    {
        try {
            $query = Database::getPDO()->prepare('SELECT * FROM topic WHERE name = ?');
            $query->execute([htmlspecialchars($name)]);
            $topic = $query->fetchObject(Topic::class);

            return $topic ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getTopicById($id): ?Topic
    {
        try {
            $query = Database::getPDO()->prepare('SELECT * FROM topic WHERE id = ?');
            $query->execute([htmlspecialchars($id)]);
            $topic = $query->fetchObject(Topic::class);

            return $topic ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getAllTopics(): ?array
    {
        try {
            $query = Database::getPDO()->query('SELECT name FROM topic ORDER BY topic.name ASC');
            $topics = $query->fetchAll(PDO::FETCH_CLASS, Topic::class);

            return $topics;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function deleteTopic($topicId): string
    {
        $query = Database::getPDO()->prepare('DELETE FROM topic WHERE id = ?');
        $query->execute([$topicId]);

        return "the topic has been successfully deleted";
    }

    public function addTopic($name): string
    {
        try {
            $newTopic = mb_strtolower(str_replace(' ', '_', $name));
            // On vérifie si le topic existe déjà
            if ($this->getTopicByName($newTopic)) {
                throw new BadRequestException("The topic already exist"); // Si le topic existe, on ne l'ajoute pas
            }

            // On insère le nouveau topic
            $query = Database::getPDO()->prepare('INSERT INTO topic (name) VALUES (?)');
            $query->execute([htmlspecialchars($newTopic)]);

            return "the topic $name has been successfully added";

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}