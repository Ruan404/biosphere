<?php

namespace App\Topic;

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
            $slug = explode('/', rtrim(htmlspecialchars($name), '/'));

            $this->name = array_pop($slug);
            
        }
    }
}