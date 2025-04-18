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
            $query = Database::getPDO()->query('SELECT * FROM topic ORDER BY topic.name ASC');
            $topics = $query->fetchAll(PDO::FETCH_CLASS, Topic::class);

            return $topics;
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function deleteTopic($topicId): string
    {
        try {
            $topic = $this->getTopicById($topicId);
            if ($topic === null) {
                throw new NotFoundException("Topic could not be found");
            }
            $query = Database::getPDO()->prepare('DELETE FROM topic WHERE id = ?');
            $query->execute([$topic->id]);

            return "$topic->name has been successfully deleted";

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function addTopic($name): string
    {
        try {
            // On vérifie si le topic existe déjà
            if ($this->getTopicByName($name)) {
                throw new BadRequestException("The topic already exist"); // Si le topic existe, on ne l'ajoute pas
            }

            // On insère le nouveau topic
            $query = Database::getPDO()->prepare('INSERT INTO topic (name) VALUES (?)');
            $query->execute([htmlspecialchars($name)]);

            return "the topic $name has been successfully added";

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}