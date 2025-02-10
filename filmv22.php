<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Films disponibles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            text-align: center;
        }
        h2 {
            margin-top: 20px;
        }
        img {
            width: 200px;
            height: 150px;
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1>Films disponibles :</h1>
    <?php
        
        session_start();
        $fichier_csv = 'infos.csv';
        $lignes = file($fichier_csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        array_shift($lignes); // Supprimez la première ligne

        foreach ($lignes as $ligne) {
            $infos = explode(';', $ligne);
            $id = $infos[0];
            $nom = $infos[1];
            $genre = $infos[2];
            $description = $infos[3];
            $chemin_image = htmlspecialchars($infos[4]);
            $chemin_film = htmlspecialchars($infos[5]);
            


            echo '<h2>'. $nom. '</h2>';
            echo '<p>Genre : '. $genre. '</p>';
            echo '<p>'. $description. '</p>';
            echo '<a href="'. $chemin_film. '"><img src="'. $chemin_image. '" alt="'. $nom. '"></a>';
        }
   ?>
</body>
</html>
