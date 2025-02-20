<?php

use App\Film\Film;
use App\Database\Database;

$style = "film";

$query = Database::getPDO()->query('SELECT * FROM film JOIN genre ON film.genre_id = genre.id');

$films = $query->fetchAll(PDO::FETCH_CLASS, Film::class);

?>

<div class="films">
    <?php foreach ($films as $film): ?>
        <div class="film-card">

            <div class="film-cover">
                <img src=<?= '/assets/images/'.$film -> getCover() ?> />
            </div>
            <a href="#"><?= $film -> getTitle() ?></a>

        </div>
    <?php endforeach ?>
</div>