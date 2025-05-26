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
                    <div class="conversation">
                        <div class="title">
                            <h2>Conversation avec <?= htmlspecialchars($data["recipient"]) ?></h2>
                        </div>
                        <div class="messages" id="ctn-action-menu">
                            <?php foreach ($messages as $message): ?>
                                <message-bubble recipient="<?= $data["recipient"] ?>"
                                    class="<?= $message->isAuthor ? 'bubble-message right' : 'bubble-message' ?>"
                                    content='<?= $message->htmlMessage ?>' date="<?= $message->date ?>"
                                    <?= $message->canDelete ? 'can-delete' : "" ?>></message-bubble>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <form id="form" method="POST" class="send-message-form">
                         <chat-input></chat-input>
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
<script type="module" src="/assets/js/components/ChatInput.js"></script>
<script>
    
    const form = document.getElementById('form')
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      const formData = new FormData(form);
    
      // Send formData to server via fetch
      fetch("/message?user=<?= htmlspecialchars($data["recipient"] ?? "") ?>", {
        method: 'POST',
        body: formData
      }).then(response => response.json())
        .then(result => {
            const newMessage = result["newMessage"];
            let message = document.createElement("message-bubble");
            message.setAttribute("recipient", "<?= htmlspecialchars($data["recipient"] ?? "") ?>")
            message.setAttribute("date", newMessage.date)
            message.setAttribute("content", newMessage.htmlMessage)
            message.setAttribute("class", "bubble-message")
            message.classList.add("right")
            message.setAttribute("can-delete", newMessage.canDelete)
            document.querySelector(".messages").appendChild(message)

        })
        .catch(err => console.error(err));
    });
</script>