<?php

namespace App\Film;

//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Film
{

    private $id;
    private $cover;
    private $video;
    private $genre_id;
    private $title;
    private $description;
    

    public function getCover(){
        return htmlspecialchars($this -> cover);
    }

    public function getVideo(){
        return htmlspecialchars($this -> video);
    }

    public function getGenre(){
        return htmlspecialchars($this -> genre_id);
    }
    
    public function getTitle(){
        return htmlspecialchars($this -> title);
    }

    public function getDesc(){
        return htmlspecialchars($this -> description);
    }

}