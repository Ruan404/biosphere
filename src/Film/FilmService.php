<?php

namespace App\Film;
use App\Core\Database;
use PDO;

class FilmService
{

    public function getAllFilms():?array
    {
        $query = Database::getPDO()->query('SELECT cover, title FROM film JOIN genre ON film.genre_id = genre.id');

        $films = $query->fetchAll(PDO::FETCH_CLASS, Film::class);

        return $films;
    }

    public function getFilmByTitle($title): ?array{
        $query = Database::getPDO()->prepare('SELECT film.title, film.cover, film.video, film.description, genre.name FROM film JOIN genre ON film.genre_id = genre.id WHERE film.title = :title');
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->execute();
        $film = $query->fetch(PDO::FETCH_ASSOC);

        if($film){
            return $film;
        }

        return null;
        
    }
}