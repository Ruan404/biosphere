<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Message\MessageService;
use App\Auth\AuthService;

$style = "message";
$messageService = new MessageService();
$authService = new AuthService();

if (!isset($_SESSION['username'], $_SESSION['user_id'])) {
    header('Location: /login');
    exit();
}

$currentUser = $_SESSION['username'];
$currentUserId = $_SESSION['user_id'];
$users = $messageService->getUsers();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie</title>
    <link rel="stylesheet" href="/assets/css/message.css">
</head>
<body>

<header>
</header>
<div class="container">
	<div class="user-list">
    	<h2>Utilisateurs disponibles</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <a href="?user_id=<?= $user['id'] ?>"><?= htmlspecialchars($user['pseudo']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<div class="message">
	<div class="no-user">aucun utilisateur sélectionné</div>
</div>
<?php if (isset($_GET['user_id'])): ?>
    <?php
        $recipientId = (int) $_GET['user_id'];
        $messages = $messageService->getMessages($recipientId);
        $recipient = $messageService->getUserById($recipientId);
    ?>

    <?php if ($recipient): ?>
        <div class="conversation">
            <h2>Conversation avec <?= htmlspecialchars($recipient['pseudo']) ?></h2>

            <div class="messages">
                <?php foreach ($messages as $message): ?>
                    <div class="message" data-id="<?= $message['id'] ?>">
                        <p>
                            <strong><?= htmlspecialchars($message['pseudo']) ?>:</strong>
                            <?= nl2br(htmlspecialchars($message['message'])) ?>
                        </p>
                        <small><?= $message['date'] ?></small>
                        <?php if ($message['id_auteur'] === $currentUserId || $_SESSION['role'] === 'admin'): ?>
                            <button onclick="deleteMessage(<?= $message['id'] ?>)">Supprimer</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="POST" action="/message/<?= $recipientId ?>">
                <textarea name="message" placeholder="Écrivez votre message..." required></textarea>
                <button type="submit">Envoyer</button>
            </form>
        </div>
    <?php else: ?>
        <p>Utilisateur introuvable.</p>
    <?php endif; ?>
<?php endif; ?>

<script>
    // Récupère le user_id depuis l'URL (attendu comme segment)
    const urlParams = new URLSearchParams(window.location.search);
    const userIdParam = urlParams.get('user_id');
    const userId = userIdParam ? userIdParam : '';

    function deleteMessage(messageId) {
        if (confirm("Êtes-vous sûr de vouloir supprimer ce message ?")) {
            // Utilise l'URL avec segment : /message/<user_id>
            const url = `/message/${userId}`;

            fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ message_id: messageId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.querySelector(`.message[data-id='${messageId}']`)?.remove();
                } else {
                    alert("Erreur : " + data.message);
                }
            })
            .catch(() => alert("Erreur lors de la suppression."));
        }
    }
</script>

</body>
</html>
