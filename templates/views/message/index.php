<?php
$style = "message";

$users = $data["users"] ?? [];
$messages = $data["messages"] ?? [];
$recipient = $data["recipient"] ?? null;
?>
<main>
    <div class="container">
        <!-- Sidebar: Contact list -->
        <sidebar-tab class="sidebar-ctn" id="contact-bar">
            <button id="toggle-tab" slot="trigger" class="tab-btn shadow-btn">Contacts</button>
            <h2 class="sidebar-title" slot="title">Utilisateurs disponibles</h2>

            <?php foreach ($users as $user): ?>
                <a slot="menu" class="sidebar-menu-button" href="?user=<?= urlencode($user->pseudo) ?>">
                    <img class="user-profil-img"
                         src="<?= htmlspecialchars($user->image) ?>"
                         alt="Avatar de <?= htmlspecialchars($user->pseudo) ?>">
                    <span class="user-name"><?= htmlspecialchars($user->pseudo) ?></span>
                </a>
            <?php endforeach; ?>
        </sidebar-tab>

        <!-- Main chat area -->
        <div class="conversation-container">
            <?php if (isset($_GET["user"])): ?>
                <?php if ($recipient): ?>
                    <div class="conversation full-page">
                        <div class="title">
                            <img class="user-profil-img"
                                 src="<?= htmlspecialchars($recipient["image"]) ?>"
                                 alt="Avatar de <?= htmlspecialchars($recipient["pseudo"]) ?>">
                            <h2>Conversation avec <?= htmlspecialchars($recipient["pseudo"]) ?></h2>
                        </div>

                        <div class="messages" id="ctn-action-menu">
                            <?php foreach ($messages as $message): ?>
                                <message-bubble
                                    recipient="<?= htmlspecialchars($recipient["pseudo"]) ?>"
                                    class="<?= $message->isAuthor ? 'bubble-message right' : 'bubble-message' ?>"
                                    content="<?= htmlspecialchars($message->message) ?>"
                                    date="<?= htmlspecialchars($message->date) ?>"
                                    message-id="<?= htmlspecialchars($message->id) ?>"
                                    <?= $message->canDelete ? 'can-delete' : '' ?>>
                                </message-bubble>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <form method="POST" action="/message?user=<?= urlencode($recipient["pseudo"]) ?>" class="send-message-form">
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