<?php
$style = "message";
use App\User\UserService;

$users = $data["users"] ?? null;
$messages = $data["messages"] ?? [];
$currentUser = $_SESSION["user"] ?? null;
$userService = new UserService();
?>
<main>
    <div class="container">
        <sidebar-tab class="sidebar-ctn" id="contact-bar">
            <button id="toggle-tab" slot="trigger" class="tab-btn shadow-btn">Contacts</button>
            <h2 class="sidebar-title" slot="title">Utilisateurs disponibles</h2>
            <?php foreach ($users as $user): ?>
                <a slot="menu" class='sidebar-menu-button' href="?user=<?= $user['pseudo'] ?>">
                    <img class="user-profil-img"
                        src="<?= htmlspecialchars($userService->getAvatarUrl($user['image'] ?? null, $user['pseudo'] ?? null)) ?>"
                        alt="Avatar de <?= htmlspecialchars($user['pseudo']) ?>">
                    <span class="user-name"><?= htmlspecialchars($user['pseudo']) ?></span>
                </a>
            <?php endforeach; ?>
            <div slot="menu" class="sidebar-menu-button user-row">
                <img class="user-profil-img"
                    src="<?= htmlspecialchars($userService->getAvatarUrl(null, $currentUser)) ?>"
                    alt="Avatar de <?= htmlspecialchars($currentUser['pseudo'] ?? 'Utilisateur') ?>">
                <span class="user-name"><?= htmlspecialchars($currentUser['pseudo'] ?? 'Utilisateur') ?></span>
            </div>
        </sidebar-tab>
        <div class="conversation-container">
            <?php if (isset($_GET["user"])): ?>
    
                <?php if ($data["recipient"]): ?>
                    <div class="conversation full-page">
                        <?php 
                            $recipient = $data["recipient"];
                            if (!is_array($recipient)) {
                                // Si recipient n'est qu'un pseudo (string), on le convertit en tableau minimal
                                $recipient = [
                                    "pseudo" => $recipient,
                                    "image" => null // image inconnue, on laisse la méthode gérer
                                ];
                            }
                            // Génère bien l'URL de l'avatar (image personnalisée OU fallback sur pseudo)
                            $recipientAvatar = $userService->getAvatarUrl($recipient["image"] ?? null, $recipient["pseudo"] ?? null);
                        ?>
                        <div class="title">
                            <img class="user-profil-img"
                                src="<?= htmlspecialchars($recipientAvatar) ?>"
                                alt="Avatar de <?= htmlspecialchars($recipient["pseudo"]) ?>">
                            <h2>Conversation avec <?= htmlspecialchars($recipient["pseudo"]) ?></h2>
                        </div>
                         <div class="messages" id="ctn-action-menu">
                            <?php foreach ($messages as $message): ?>
                               <message-bubble recipient="<?= $recipient["pseudo"] ?>" class="<?= $message->isAuthor ? 'bubble-message right' : 'bubble-message' ?>"
                                    content="<?= $message->message ?>" date="<?= $message->date ?>"
                                    message-id="<?= $message->id ?>" <?= $message->canDelete ? 'can-delete' : ""  ?>></message-bubble>
                            <?php endforeach; ?>
                        </div>
                    </div>
                     <form method="POST" action="/message?user=<?= $recipient["pseudo"] ?>" class="send-message-form">
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
</main>
<script src="/assets/js/components/SideBar.js"></script>
<script type="module" src="/assets/js/components/MessageBubble.js"></script>