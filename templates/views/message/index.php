<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Message\MessageService;
use App\Auth\AuthService;
use App\Helpers\Text;

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


<div class="container">

    <sidebar-tab class="sidebar-ctn" id="contact-bar">
        <h2 class="sidebar-title" slot="title">Utilisateurs disponibles</h2>
        <?php foreach ($users as $user): ?>
            <a slot="menu" class='sidebar-menu-button'
                href="?user_id=<?= $user['id'] ?>"><?= htmlspecialchars($user['pseudo']) ?></a>

        <?php endforeach; ?>
    </sidebar-tab>
    <div class="conversation-container">
        <?php if (isset($_GET['user_id'])): ?>
            <?php
            $recipientId = (int) $_GET['user_id'];
            $messages = $messageService->getMessages($recipientId);
            $recipient = $messageService->getUserById($recipientId);
            ?>
            <?php if ($recipient): ?>
                <div class="conversation full-page">
                    <div class="title">
                        <div class="tab-users mobile-only">
                            <button id="toggle-tab" class="tab-btn shadow-btn">Contacts</button>

                        </div>
                        <h2>Conversation avec <?= htmlspecialchars($recipient['pseudo']) ?></h2>
                    </div>
                    <div class="messages">
                        <?php foreach ($messages as $message): ?>
                            <message-bubble class="<?= $message['id_auteur'] === $currentUserId ? 'bubble right' : 'bubble' ?>"
                                content="<?= htmlspecialchars($message['message']) ?>" date="<?= $message['date'] ?>"
                                message-id="<?= $message['id'] ?>" <?= $message['id_auteur'] === $currentUserId || $_SESSION['role'] === 'admin' ? 'can-delete' : '' ?>></message-bubble>
                        <?php endforeach; ?>
                    </div>
                </div>
                <form method="POST" action="/message/<?= $recipientId ?>" class="send-message-form">
                    <textarea name="message" placeholder="Écrivez votre message..." required></textarea>
                    <button class="primary-btn" type="submit">Envoyer</button>
                </form>
            <?php else: ?>
                <p>Utilisateur introuvable.</p>
            <?php endif; ?>
        <?php else: ?>
            <div class="no-user">Aucun utilisateur sélectionné</div>
        <?php endif; ?>
    </div>
</div>
<script src="/assets/js/components/SideBar.js"></script>
<script src="/assets/js/components/MessageBubble.js"></script>
<script>
    document.getElementById('toggle-tab').addEventListener('click', () => {
        const sidebar = document.getElementById('contact-bar');
        sidebar.open(); // this will now work!
    });
</script>