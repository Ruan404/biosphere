<?php
use App\Film\FilmService;

$style = "film";

$filmDetails = $data['films'] ?? []
?>
<div class="film-ctn">
    <div class="film-card">
        <video controls controlsList="nodownload">
            <source src="<?= '/assets/videos/' . $filmDetails["video"] ?>" type="video/mp4">
            Votre navigateur ne supporte pas la balise vidéo.
        </video>
        <p><?= $filmDetails["title"] ?></p>
    </div>
</div>