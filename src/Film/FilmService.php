<?php

namespace App\Film;

use App\Core\Database;
use PDO;

class FilmService
{

    public function getAllFilms(): ?array
    {
        $query = Database::getPDO()->query('SELECT cover, title FROM film JOIN genre ON film.genre_id = genre.id');

        $films = $query->fetchAll(PDO::FETCH_CLASS, Film::class);

        return $films;
    }

    public function getFilmByTitle($title): ?array
    {
        $query = Database::getPDO()->prepare('SELECT * FROM film JOIN genre ON film.genre_id = genre.id WHERE film.title = :title');
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->execute();
        $film = $query->fetch(PDO::FETCH_ASSOC);

        if ($film) {
            return $film;
        }

        return null;
    }

    public function deleteFilm($filmId)
    {
        $query = Database::getPDO()->prepare('DELETE FROM film WHERE id = ?');
        $result = $query->execute([$filmId]);

        return $result;
    }
}
