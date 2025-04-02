<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
    header('Location: login.php');
    exit;
}

// Vérifier si l'ID du destinataire est dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "Aucun identifiant trouvé";
    exit;
}

$getid = $_GET['id'];

// Vérifier si l'utilisateur destinataire existe
$recupUser = $bdd->prepare('SELECT * FROM users WHERE id = ?');
$recupUser->execute(array($getid));

if ($recupUser->rowCount() == 0) {
    echo "Aucun utilisateur trouvé";
    exit;
}

// Vérifier le rôle de l'utilisateur
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');

// Envoyer un message
if (isset($_POST['envoyer'])) {
    $message = nl2br(htmlspecialchars($_POST['message']));
    $insertMessage = $bdd->prepare('INSERT INTO messages_privés(message, id_destinataire, id_auteur) VALUES(?, ?, ?)');
    $insertMessage->execute(array($message, $getid, $_SESSION['id']));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Messages privés</title>
    <style>
        #messages {
            height: 300px;
            overflow-y: auto;
        }

        .message {
            margin-bottom: 10px;
            position: relative;
        }

        .ellipsis {
            font-size: 18px;
            color: #333;
            background: none;
            border: none;
            cursor: pointer;
        }

        .delete-options {
            display: none;
            position: absolute;
            left: 0; /* Aligner sous le bouton */
            top: 100%; /* Placer en bas des trois points */
            background: white;
            border: 1px solid #ccc;
            padding: 5px;
            box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.2);
            z-index: 100; /* S'assurer qu'il est au-dessus des autres éléments */
        }

        /* Confirmation de suppression */
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
    </style>
</head>

<body>
    <h1>Messagerie Privée</h1>

    <form method="POST" action="">
        <textarea name="message" autocomplete="off" placeholder="Entrez votre message"></textarea>
        <br><br>
        <input type="submit" name="envoyer" value="Envoyer">
    </form>

    <section id="messages">
        <?php  
            $recupMessages = $bdd->prepare('SELECT * FROM messages_privés WHERE (id_auteur = ? AND id_destinataire = ?) OR (id_auteur = ? AND id_destinataire = ?)');
            $recupMessages->execute(array($_SESSION['id'], $getid, $getid, $_SESSION['id']));
            
            while ($message = $recupMessages->fetch()) {
        ?>
                <div class="message">
                    <p style="<?= $message['id_destinataire'] == $_SESSION['id'] ? '' : 'color:blue;' ?>"><?= $message['message']; ?></p>

                    <!-- Si l'utilisateur est administrateur, afficher les options de suppression -->
                    <?php if ($isAdmin) { ?>
                        <button class="ellipsis" onclick="toggleDeleteOptions(<?= $message['id']; ?>)">...</button>
                        <div class="delete-options" id="delete-options-<?= $message['id']; ?>">
                            <button onclick="confirmDelete(<?= $message['id']; ?>)">Supprimer</button>
                        </div>
                    <?php } ?>
                </div>
        <?php
            }
        ?>
    </section>

    <!-- Fenêtre de confirmation de suppression -->
    <div id="confirm-delete" class="confirm-delete">
        <p>Êtes-vous sûr de vouloir supprimer ce message ?</p>
        <button onclick="deleteMessage()">Oui</button>
        <button onclick="cancelDelete()">Non</button>
    </div>

    <script>
        // Afficher les options de suppression
        function toggleDeleteOptions(messageId) {
            let deleteOptions = document.getElementById('delete-options-' + messageId);
            deleteOptions.style.display = deleteOptions.style.display === 'none' ? 'block' : 'none';
        }

        // Afficher la fenêtre de confirmation
        function confirmDelete(messageId) {
            let confirmDeleteModal = document.getElementById('confirm-delete');
            confirmDeleteModal.style.display = 'flex';

            // Stocker l'ID du message à supprimer dans une variable globale
            window.messageToDelete = messageId;
        }

        // Annuler la suppression
        function cancelDelete() {
            let confirmDeleteModal = document.getElementById('confirm-delete');
            confirmDeleteModal.style.display = 'none';
        }

        // Effectuer la suppression du message
        function deleteMessage() {
            window.location.href = 'delete_message.php?id=' + window.messageToDelete;
        }
    </script>

</body>
</html>
