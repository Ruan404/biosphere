<?php
$style = "message";

$users = $data["users"] ?? null;
$messages = $data["messages"] ?? [];
?>
<main>
    <div class="container">
        <sidebar-tab class="sidebar-ctn" id="contact-bar">
            <button id="toggle-tab" slot="trigger" class="tab-btn shadow-btn">Contacts</button>
            <h2 class="sidebar-title" slot="title">Utilisateurs disponibles</h2>
            <?php foreach ($users as $user): ?>
                <a slot="menu" class='sidebar-menu-button'
                    href="?user=<?= $user->pseudo ?>"><?= htmlspecialchars($user->pseudo) ?></a>

            <?php endforeach; ?>
        </sidebar-tab>
        <div class="conversation-container">
            <?php if (isset($_GET["user"])): ?>
               
                <?php if ($data["recipient"]): ?>
                    <div class="conversation full-page">
                        <div class="title">
                            <h2>Conversation avec <?= htmlspecialchars($data["recipient"]) ?></h2>
                        </div>
                        <div class="messages" id="ctn-action-menu">
                            <?php foreach ($messages as $message): ?>
                                <message-bubble recipient="<?= $data["recipient"] ?>" class="<?= $message->isAuthor ? 'bubble-message right' : 'bubble-message' ?>"
                                    content="<?= $message->message ?>" date="<?= $message->date ?>"
                                    message-id="<?= $message->id ?>" <?= $message->canDelete ? 'can-delete' : ""  ?>></message-bubble>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <form method="POST" action="/message?user=<?= $data["recipient"] ?>" class="send-message-form">
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