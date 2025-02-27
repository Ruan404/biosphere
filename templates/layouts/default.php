<?php
use \App\User\UserService;
use \App\Helpers\Text;

session_start();
/**
 * 0 ----> PHP_SESSION_DISABLED if sessions are disabled.
 * 1 ----> PHP_SESSION_NONE if sessions are enabled, but none exists.
 * 2 ----> PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
 */

if (session_status() == 1 || empty($_SESSION['auth'])) {
    header("Location: " . $router->url('login'));
    exit();
}

$userService = new UserService();

$user = $userService->getUserById($_SESSION['auth'])->pseudo;

$profile = Text::getFirstStr($user); //à développer
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
                    <a href="#">Messagerie</a>
                    <a href="/chat">Forum</a>
                    <a href="/films">Films</a>
                    <a href="/podcast">Podcast</a>
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