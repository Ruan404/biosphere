<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
    header('Location: /login.php');
    exit;
}

// Vérifier le rôle de l'utilisateur
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');

// Récupérer tous les utilisateurs sauf l'utilisateur connecté
$recupUser = $bdd->prepare('SELECT * FROM users WHERE id != :id');
$recupUser->execute(['id' => $_SESSION['id']]);

?>

<!DOCTYPE html lang="fr">
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion des utilisateurs</title>
    <style>
        #messages {
            height: 300px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
        }

        a {
            text-decoration: none;
            color: black;
        }

        .ellipsis {
            font-size: 18px;
            color: #333;
            background: none;
            border: none;
            cursor: pointer;
        }

        .delete-link {
            color: red;
            font-weight: bold;
            cursor: pointer; /* Change le curseur en main pour simuler un lien */
        }

        /* Ajouter un style pour la confirmation de suppression */
        .confirm-delete {
            display: none;
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .confirm-delete button {
            margin: 10px;
        }

        .delete-options {
            display: none;
        }
    </style>
</head>
<body>

<h1>Gestion des utilisateurs</h1>

<?php
// Boucle pour afficher les utilisateurs
while ($user = $recupUser->fetch()) {
?>

    <a href="conversations_privées.php?id=<?php echo $user['id']; ?>">
        <p><?php echo $user['pseudo']; ?></p>
    </a>

    <!-- Si l'utilisateur connecté est un administrateur, afficher les options de suppression -->
    <?php if ($isAdmin) { ?>
        <!-- Trois points de suspension -->
        <button class="ellipsis" onclick="toggleDeleteOptions(<?php echo $user['id']; ?>)">...</button>

        <!-- Options de suppression -->
        <div class="delete-options" id="delete-options-<?php echo $user['id']; ?>">
            <button onclick="confirmDelete(<?php echo $user['id']; ?>)">Supprimer l'utilisateur</button>
        </div>
    <?php } ?>

    <hr> <!-- Séparateur entre les utilisateurs -->

<?php
}
?>

<!-- Fenêtre de confirmation de suppression -->
<div id="confirm-delete" class="confirm-delete">
    <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
    <button onclick="deleteUser()">Oui</button>
    <button onclick="cancelDelete()">Non</button>
</div>

<!-- Inclure le script pour la gestion des options de suppression -->
<script>
    // Afficher les options de suppression
    function toggleDeleteOptions(userId) {
        const deleteOptions = document.getElementById('delete-options-' + userId);
        deleteOptions.style.display = deleteOptions.style.display === 'none' ? 'block' : 'none';
    }

    // Afficher la fenêtre de confirmation
    function confirmDelete(userId) {
        const confirmDeleteModal = document.getElementById('confirm-delete');
        confirmDeleteModal.style.display = 'flex';

        // Enregistrer l'ID de l'utilisateur à supprimer dans un attribut global
        window.userToDelete = userId;
    }

    // Annuler la suppression
    function cancelDelete() {
        const confirmDeleteModal = document.getElementById('confirm-delete');
        confirmDeleteModal.style.display = 'none';
    }

    // Effectuer la suppression de l'utilisateur
    function deleteUser() {
        window.location.href = 'delete_user.php?id=' + window.userToDelete;
    }
</script>

</body>
</html>
