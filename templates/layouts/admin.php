<?php
use App\Helpers\Text;

if (session_status() === 1) {
    session_start();
}

//l'utilisiteur n'est pas connecté
if (!$_SESSION) {
    header('Location: /login');
    exit();
}

$profile = Text::getFirstStr($_SESSION["username"]);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'biosphere') ?></title>
    <meta name="description" content="<?= htmlspecialchars($description ?? 'bienvenu dans le biosphere') ?>">

    <link rel="stylesheet" type="text/css" href="/assets/css/navbar.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/admin.css">
    <?php if (isset($style)): ?>
        <link rel="stylesheet" type="text/css" href=<?= '/assets/css/' . htmlspecialchars($style) . '.css' ?>>
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
                    <a href="/admin">Admin</a>
                </div>
                <div class="user-profile">
                    <span class="user-pofile-frame"><?= $profile ?></span>
                    <a class="primary-btn" href="/logout">se déconnecter</a>
                </div>
            </div>
        </nav>
    </header>
    <?= $content ?>
    <script src="/assets/js/script.js"></script>
</body>

</html>