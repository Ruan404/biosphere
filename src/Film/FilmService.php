<?php

namespace App\Film;
use App\Database\Database;
use PDO;

class FilmService
{

    public function getAllFilms():?array
    {
        $query = Database::getPDO()->query('SELECT * FROM film JOIN genre ON film.genre_id = genre.id');

        $films = $query->fetchAll(PDO::FETCH_CLASS, Film::class);

        return $films;
    }
}