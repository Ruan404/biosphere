<?php
use App\Entities\Role;

$username = $_SESSION["username"];
$role = $_SESSION["role"];
$roles = [Role::Admin];
$avatarUrl = $_SESSION["avatar"];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'title') ?></title>
    <meta name="description" content="<?=htmlspecialchars($description ?? 'bienvenu dans le biosphere')?>">

    <link rel="stylesheet" type="text/css" href="/assets/css/navbar.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <?php if (isset($style)): ?>
        <link rel="stylesheet" type="text/css" href=<?= '/assets/css/' . htmlspecialchars($style) . '.css' ?>>
    <?php endif ?>
    <script src="/assets/js/config.js"></script>
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
                    <a href="/message">Messagerie</a>
                    <a href="/chat">Forum</a>
                    <a href="/films">Films</a>
                    <a href="/podcast">Podcast</a>
                    <a href="/sensors">Capteurs</a>
                    <?php if($role && in_array(Role::tryFrom($role), $roles)) : ?>
                        <a href="/admin">Admin</a>
                    <?php endif ?>
                </div>
                <div class="user-profile profil-dropdown">
                    <button class="profil-avatar-btn" id="profilAvatarBtn">
                        <img class="user-profile-img" src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($username) ?>">
                    </button>
                    <div class="profil-menu" id="profilMenu">
                        <a class="primary-btn" href="/profile">Mon avatar</a>
                        <a class="primary-btn" href="/logout">Se déconnecter</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <?= $content ?? "" ?>
    <script src="/assets/js/script.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const avatarBtn = document.getElementById("profilAvatarBtn");
        const dropdown = avatarBtn.closest('.profil-dropdown');
        avatarBtn.addEventListener("click", function(e) {
            e.stopPropagation();
            dropdown.classList.toggle("open");
        });
        document.addEventListener("click", function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove("open");
            }
        });
    });
    </script>
</body>
</html>