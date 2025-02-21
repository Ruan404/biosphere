<?php
use App\Film\FilmService;

$style = "film";

$films = new FilmService()->getAllFilms();

?>

<div class="films">
    <?php foreach ($films as $film): ?>
        <div class="film-card">

            <div class="film-cover">
                <img src=<?= '/assets/images/' . $film->cover ?> />
            </div>
            <a href="#"><?= $film->title ?></a>

        </div>
    <?php endforeach ?>
</div>