<?php

namespace App\Topic;

use App\Database\Database;

//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Topic
{

    public int $id{
        get => $this->id;
    }

    public string $name
    {
        get => htmlspecialchars($this->name);
        set(string $name){
            $slug = explode('/', rtrim($name, '/'));

            $this->name = array_pop($slug);
            
        }
    }

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
}