
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Film : <?php echo $_GET['film'];?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        header {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 20px;
        }
        nav a {
            margin: 0 10px;
            text-transform: uppercase;
            text-decoration: none;
            color: #333;
        }
        main {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            align-items: center;
            padding: 20px;
        }
        h1 {
            font-size: 2.5em;
            text-transform: uppercase;
            letter-spacing: 2px;
            text-align: center;
        }
       .film {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
       .film img {
            width: 200px;
            height: 100%;
            object-fit: cover;
            border-radius: 10px 0 0 10px;
        }
       .film h1 {
            font-size: 1.5em;
            margin-top: 0;
            margin-left: 20px;
        }
    </style>
</head>
<body oncontextmenu="return false;">
    <header>
        <nav>
            <a href="podcast.php">Podcast</a>
            <a href="conversations_index.php">Messagerie</a>
            <a href="chat.php">Forum</a>
            <a href="film.php">Films</a>
            <a href="connexion.php">Connexion</a>
            <a href="deconnexion.php">Déconnexion</a>
        </nav>
    </header>

    <main>
        <div class="film">
            <div class="infos">
                <h1>Film : <?php echo basename($_GET['film']);?></h1>
                <video controls controlsList="nodownload">
                    <source src="<?php echo $_GET['film'];?>" type="video/mp4">
                    Votre navigateur ne supporte pas la balise vidéo.
                </video>
            </div>
        </div>
    </main>
    <footer>
        <?php echo filesize('lecteur_video.php'). 'ytes';?>
    </footer>
</body>
</html>
