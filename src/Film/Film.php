<?php

namespace App\Film;

//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Film
{
    public string $cover{
        get => htmlspecialchars($this -> cover);
    }
    
    public string $video{
        get => htmlspecialchars($this -> video);
    }

    public int $genre_id{
        get => $this -> genre_id;
    }
    public int $id = 0{
        get => $this->id;
    }

    public string $title{
        get => htmlspecialchars($this -> title);
    }
    public string $description{
        get => htmlspecialchars($this -> description);
    }

}