<?php
namespace App\Topic;
use App\Core\Database;
use App\Topic\Dto\TopicAdminPanelDto;
use Exception;
use PDO;
use PDOException;
use function PHPUnit\Framework\throwException;

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

    public function adminTopics(): ?array
    {
        try {
            $query = Database::getPDO()->query('SELECT name FROM topic ORDER BY topic.name ASC');
            $topics = $query->fetchAll(PDO::FETCH_CLASS, TopicAdminPanelDto::class);

            return $topics;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function deleteTopic($topicId): bool
    {
        try {
            $query = Database::getPDO()->prepare('DELETE FROM topic WHERE id = ?');
            $query->execute([$topicId]);

            return true;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function addTopic($name): bool
    {
        try {
            $newTopic = mb_strtolower(str_replace(' ', '_', $name));

            // On insère le nouveau topic
            $query = Database::getPDO()->prepare('INSERT INTO topic (name) VALUES (?)');
            $query->execute([htmlspecialchars($newTopic)]);

            return true;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    /**
     * recupérer les topics en donnant leurs noms
     * @param array $topics
     * @throws \Exception
     * @return array|null
     */
    public function getTopicsByNames(array $names): array|null
    {
        try {
            $in = str_repeat('?,', times: count($names) - 1) . '?';
            
            $query = Database::getPDO()->prepare("SELECT * FROM topic WHERE name IN ($in)");
            $query->execute($names);

            $topics = $query->fetchAll(PDO::FETCH_ASSOC);
           
            return $topics ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function deleteTopics(array $topics): bool
    {

        try {
            $in = str_repeat('?,', count($topics) - 1) . '?';

            $query = Database::getPDO()->prepare("DELETE FROM topic WHERE id IN ($in)");
            $query->execute($topics);

            return true;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}