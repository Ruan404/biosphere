<?php
namespace App\Topic;
use App\Core\Database;
use PDO;

class TopicService extends Topic
{
    public function getTopicByName($name): ?Topic
    {
        $this->name = $name; //set a name

        $query = Database::getPDO()->prepare('SELECT * FROM topic WHERE name = ?');
        $query->execute([$this->name]);
        $topic = $query->fetchObject(Topic::class);

        if ($topic) {
            return $topic;
        }

        return null;
    }

    public function getAllTopics(): ?array
    {
        $query = Database::getPDO()->query('SELECT * FROM topic ORDER BY topic.name ASC');
        $topics = $query->fetchAll(PDO::FETCH_CLASS, Topic::class);

        return $topics;
    }

    public function deleteTopic($topicId)
    {
        $query = Database::getPDO()->prepare('DELETE FROM topic WHERE id = ?');
        $result = $query->execute([$topicId]);

        return $result;
    }
}