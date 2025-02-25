<?php

use App\Film\FilmService;


if (isset($params['slug'])) {
    //instancier la classe FilmService
    $film = new FilmService();

    $filmDetails = $film->getFilmByTitle($params['slug']);

    $filmJson = $filmJson = json_encode($filmDetails);

    echo $filmJson;
}