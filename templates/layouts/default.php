<?php
    use App\Auth\AuthService;
    use App\Helpers\Text;
    $user = AuthService::getUserSession();

    //l'utilisiteur n'est pas connecté
    if($user == null){
       header('Location: /login');
       exit();
    }

    $profile = Text::getFirstStr($user->pseudo);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'biosphere' ?></title>
    <meta name="description" content=<?= $description ?? 'bienvenu dans le biosphere' ?>>

    <link rel="stylesheet" type="text/css" href="/assets/css/navbar.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <?php if (isset($style)): ?>
        <link rel="stylesheet" type="text/css" href=<?= '/assets/css/' . $style . '.css' ?>>
    <?php endif ?>

</head>

<body>
    <header>
        <nav>
            <div class="nav-ctn">
                <div class="logo-ctn">
                    <div class="nav-btn">
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                    <a href="/">Biosphère</a>
                </div>
                <div class="nav-links">
                    <a href="/messagerie">Messagerie</a>
                    <a href="/chat">Forum</a>
                    <a href="/films">Films</a>
                    <a href="/podcast">Podcast</a>
                    <a href="/sensors">Capteurs</a>
                </div>
                <div class="user-profile">
                    <span class="user-pofile-frame"><?= $profile ?></span>
                    <a class="primary-btn" href="/logout">se déconnecter</a>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <?= $content ?>
    </main>

    <footer>
        <?php if (defined('DEBUG_TIME')): ?>

            temps d'affichage : <?= round(1000 * (microtime(true) - DEBUG_TIME)) ?> ms

        <?php endif ?>
    </footer>
    <script src="/assets/js/script.js"></script>
</body>

</html>