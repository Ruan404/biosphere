<?php

session_start();

$fichier_csv = 'infos.csv';
$lignes = file($fichier_csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
array_shift($lignes); // Supprimez la première ligne

?>

<!DOCTYPE html lang="fr">

<html>

<head>

  <meta charset="UTF-8">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Films disponibles</title>

  <link rel="stylesheet" href="navbar.css">
  <link rel="stylesheet" href="stylesheetV21.css">
  <link rel="stylesheet" href="film.css">

</head>

<body>

  <nav>
    <div class="navbar">
      <i class="">|||</i>
      <div class="logo"><a href="home.php">Biosphere</a></div>
      <div class="nav-links">
        <div class="sidebar-logo">
          <span class="logo-name">-</span>
          <i class="">X</i>
        </div>
        <ul class="links">
          <li><a href="le_projet.php">Le projet</a></li>
          <li><a href="conversations_index.php">Messagerie</a></li>
          <li>
            <a href="#">forum </a>
            <i1 class="">v</i1>
            <ul class="htmlCss-sub-menu sub-menu">
              <li><a href="chat.php?topic=commentaires_de_film">Commentaires de film</a></li>
              <li><a href="chat.php?topic=petites_annonces">Petites annonces</a></li>
              <li><a href="chat.php?topic=evenements">événements</a></li>
              <li><a href="chat.php?topic=propositions_services">Services / Savoir faire</a></li>
              <li><a href="chat.php?topic=repair_cafe">Repair café</a></li>
            </ul>
          </li>
          <li>
            <a href="#">station meteo </a>
            <i1 class="">v</i1>
            <ul class="js-sub-menu sub-menu">
              <li><a href="station_meteo_temps_reel.php">infos temps reel</a></li>
              <li><a href="station_meteo_historique.php">historique station meteo</a></li>
              <li><a href="station_meteo_infos.php">infos</a></li>
            </ul>
          </li>
          <li><a href="film.php">Films</a></li>
          <li><a href="podcast.php">Podcast</a></li>
          <li><a href="deconnexion.php">Déconnexion</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <main>


    <?php foreach ($lignes as $ligne) { ?>

      <?php $infos = explode(';', $ligne); ?>

      <?php $id = $infos[0]; ?>

      <?php $nom = $infos[1]; ?>

      <?php $genre = $infos[2]; ?>

      <?php $description = $infos[3]; ?>

      <?php $chemin_image = htmlspecialchars($infos[4]); ?>

      <?php $chemin_film = htmlspecialchars($infos[5]); ?>

      <div class="film">

        <img src="<?php echo $chemin_image; ?>" alt="Image de la vidéo">

        <div class="infos">

          <h2><?php echo $nom; ?></h2>

          <p>Genre : <?php echo $genre; ?></p>

          <p><?php echo $description; ?></p>

          <a href="lecteur_video.php?film=<?php echo $chemin_film; ?>">Voir le film</a>

        </div>

      </div>

    <?php } ?>

  </main>

  <footer>

    <?php echo filesize('film.php') . ' bytes'; ?>

  </footer>

</body>

</html>