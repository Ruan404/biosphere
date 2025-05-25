<?php
$style = "film";
$description = "vidéos biosphère";
$title = "films";
?>

<main>
    <div class="films">
        <?php foreach ($data as $film): ?>
            <div class="film-card">
                <div class="film-cover">
                    <img src=<?= "/".$film->cover_image ?> />
                </div>
                <button class="pop-film-details-btn" onclick='fetchdata("<?= $film->token ?>")'><?= $film->title ?></button>
            </div>
        <?php endforeach ?>
        <div class="film-details">
            <div class="details-ctn">

            </div>
        </div>
    </div>
</main>
<script src="/assets/js/film.js"></script>