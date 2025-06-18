<?php
use App\Helpers\Text;

if (session_status() === 1) {
    session_start();
}

//l'utilisateur n'est pas connecté
if (!$_SESSION) {
    header('Location: /login');
    exit();
}

// Synchronise l'avatar de la session avec la base
$username = $_SESSION["username"];
$pdo = \App\Core\Database::getPDO();
$stmt = $pdo->prepare("SELECT image FROM users WHERE pseudo = ?");
$stmt->execute([$username]);
$avatarFromDb = $stmt->fetchColumn();
if ($avatarFromDb) {
    $_SESSION['avatar'] = $avatarFromDb;
}

// Avatar dynamique (uploadé ou prédéfini, sinon [lettre].png)
$avatarFilename = isset($_SESSION['avatar']) ? $_SESSION['avatar'] : (Text::getFirstStr($username) . '.png');
$avatarFile = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/avatars/' . $avatarFilename;
$version = file_exists($avatarFile) ? filemtime($avatarFile) : time();
$avatarUrl = '/uploads/images/avatars/' . $avatarFilename . '?v=' . $version;
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
                    <a href="/admin">Admin</a>
                </div>
                <div class="user-profile profil-dropdown">
                    <button class="profil-avatar-btn" id="profilAvatarBtn">
                        <img class="user-profile-img" src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($username) ?>">
                    </button>
                    <div class="profil-menu" id="profilMenu">
                        <a class="primary-btn" href="/profile">Changer mon avatar</a>
                        <a class="primary-btn" href="/logout">Se déconnecter</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <?= $content ?>
    <script src="/assets/js/script.js"></script>
    <script src="/assets/js/components/Sidebar.js"></script>
    <script type="module" src="/assets/js/components/ActionMenu.js"></script>
    <script src="/assets/js/adminPanel.js"></script>
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