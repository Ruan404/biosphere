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
            <button onclick='fetchdata("<?= $film->title ?>")'><?= $film->title ?></button>
        </div>
    <?php endforeach ?>
</div>
<div class="film-details">
    <div class="details-ctn">

    </div>
</div>
<script src="/assets/js/film.js"></script>