<?php
session_start(); // Toujours démarrer la session au début

use App\Helpers\Text;
use App\Admin\AdminService;
use App\Topic\TopicService;

$style = "message"; // Correction : le style doit être "admin" pour la gestion des utilisateurs
$adminService = new AdminService();


$users =[]; // Récupération des utilisateurs
$messages = []; // Récupération des messages

$currentTopic = isset($data['currentTopic']) ? htmlspecialchars($data['currentTopic']) : '';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion</title>
    <link rel="stylesheet" href="css/message<?= $style ?>.css"> <!-- Ajout correct du fichier CSS -->
</head>
<body>

<div class="container">
    <h1>Bienvenue, <?= htmlspecialchars($_SESSION['username'] ?? 'Utilisateur') ?> !</h1>
    
    <h2>Gestion des utilisateurs</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom d'utilisateur</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= $user['is_admin'] ? 'Administrateur' : 'Utilisateur' ?></td>
                    <td>
                        <?php if (!$user['is_admin']): ?>
                            <button class="promote-btn" data-id="<?= htmlspecialchars($user['id']) ?>">Promouvoir</button>
                        <?php endif; ?>
                        <button class="delete-user-btn" data-id="<?= htmlspecialchars($user['id']) ?>">Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <h2>Gestion des Messages</h2>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Auteur</th>
                <th>Message</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $message): ?>
                <tr>
                    <td><?= htmlspecialchars($message['id']) ?></td>
                    <td><?= htmlspecialchars($message['author']) ?></td>
                    <td><?= htmlspecialchars($message['content']) ?></td>
                    <td>
                        <button class="delete-message-btn" data-id="<?= htmlspecialchars($message['id']) ?>">Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <a href="../login">Déconnexion</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Suppression des utilisateurs
        document.querySelectorAll('.delete-user-btn').forEach(button => {
            button.addEventListener('click', function() {
                let userId = this.dataset.id;
                if (confirm("Voulez-vous vraiment supprimer cet utilisateur ?")) {
                    fetch(`delete_user.php?id=${encodeURIComponent(userId)}`, { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            location.reload();
                        });
                }
            });
        });

        // Promotion des utilisateurs
        document.querySelectorAll('.promote-btn').forEach(button => {
            button.addEventListener('click', function() {
                let userId = this.dataset.id;
                if (confirm("Promouvoir cet utilisateur en administrateur ?")) {
                    fetch(`set_admin.php?id=${encodeURIComponent(userId)}`, { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            location.reload();
                        });
                }
            });
        });

        // Suppression des messages
        document.querySelectorAll('.delete-message-btn').forEach(button => {
            button.addEventListener('click', function() {
                let messageId = this.dataset.id;
                if (confirm("Voulez-vous vraiment supprimer ce message ?")) {
                    fetch(`delete_message.php?id=${encodeURIComponent(messageId)}`, { method: 'POST' })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                            location.reload();
                        });
                }
            });
        });
    });

    function showTab() {
        document.querySelector('.topics').style.display = 'block';
    }

    function hideTab() {
        document.querySelector('.topics').style.display = 'none';
    }
</script>

</body>
</html>
