<?php
$style = "message";

$users = $data["users"] ?? [];
$recipient = htmlspecialchars($data["recipient"] ?? "");
?>
<main>
    <div class="container">
        <!-- Sidebar -->
        <sidebar-tab class="sidebar-ctn" id="contact-bar">
            <button slot="trigger" class="tab-btn shadow-btn" id="toggle-tab">Contacts</button>
            <h2 class="sidebar-title" slot="title">Utilisateurs disponibles</h2>
            <?php foreach ($users as $user): ?>
                <a slot="menu" class='sidebar-menu-button' data-slug="<?= $user->pseudo ?>"
                    onclick="viewConversation(event, '<?= $user->pseudo ?>')"
                    href="#"><?= htmlspecialchars($user->pseudo) ?></a>
            <?php endforeach; ?>
        </sidebar-tab>

        <!-- Message Container -->
        <div class="conversation-container">
            <div class="conversations <?= $recipient ? '' : 'hidden' ?>">
                <div class="messages" id="ctn-action-menu"></div>
                <form id="form" method="POST" class="send-message-form">
                    <chat-input></chat-input>
                    <button class="primary-btn" type="submit">Envoyer</button>
                </form>
            </div>
            <div class="no-user">Aucun utilisateur sélectionné</div>
        </div>
    </div>
</main>

<script src="/assets/js/components/SideBar.js"></script>
<script type="module" src="/assets/js/components/MessageBubble.js"></script>
<script type="module" src="/assets/js/components/ChatInput.js"></script>

<script>
    const form = document.getElementById('form');
    const messagesCtn = document.querySelector('.conversations');
    const msgsDisplayCtn = document.querySelector('.messages');
    const noUserCtn = document.querySelector('.no-user');
    let currentRecipient = "<?= $recipient ?>";
    const socket = new WebSocket(`${WEBSOCKET_URL}/message/${currentRecipient}`);

    function updateView(user) {
        document.title = user || "messages";
        const hasUser = Boolean(user);
        messagesCtn.classList.toggle("hidden", !hasUser);
        noUserCtn?.classList.toggle("hidden", hasUser);
    }

    window.addEventListener("DOMContentLoaded", () => {
        if (currentRecipient) {
            document.querySelector(`[data-slug="${currentRecipient}"]`)?.classList.add("current");
            fetchMessages(currentRecipient);
        }
        updateView(currentRecipient);
        history.replaceState({ user: currentRecipient }, `Messages ${currentRecipient}`, currentRecipient ? `/message?user=${currentRecipient}` : "/message");
    });

    function viewConversation(ev, user) {
        ev.preventDefault();
        if (user !== currentRecipient) {
            document.querySelector(".sidebar-menu-button.current")?.classList.remove("current");
            ev.target.classList.add("current");

            currentRecipient = user;
            history.pushState({ user }, `Messages ${user}`, `/message?user=${user}`);
            fetchMessages(user);
            updateView(user);
        }
    }

    window.addEventListener("popstate", (e) => {
        const user = e.state?.user || "";
        if (user !== currentRecipient) {
            currentRecipient = user;
            document.querySelector(".sidebar-menu-button.current")?.classList.remove("current");
            document.querySelector(`[data-slug="${user}"]`)?.classList.add("current");
            if (user) fetchMessages(user);
            else msgsDisplayCtn.innerHTML = "";
            updateView(user);
        }
    });

    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        fetch(`/message?user=${currentRecipient}`, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.newMessage) {
                    socket?.send(JSON.stringify(data));
                    displayMessage(data.newMessage);
                    scrollToBottom();
                }
            })
            .catch(console.error)
            .finally(() => form.reset());
    });

    socket.onmessage = function (event) {
        const data = JSON.parse(event.data);
        if (data.action === "delete" && data.messages) {
            data.messages.forEach(id => {
                document.querySelectorAll('message-bubble').forEach(b => {
                    if (b.getAttribute('date') === id) b.remove();
                });
            });
        }
        if (data.newMessage && data.newMessage.sender === currentRecipient && data.newMessage.recipient === "<?= $_SESSION["username"] ?>") {

            displayMessage(data.newMessage, show = false);
            scrollToBottom();
        }
    }

    function fetchMessages(user) {
        fetch(`/message?user=${user}`, { headers: { 'Accept': 'application/json' } })
            .then(res => res.json())
            .then(data => {
                msgsDisplayCtn.innerHTML = "";
                data.messages.forEach(displayMessage);
            })
            .catch(console.error);
    }

    function displayMessage(msg, show = true) {
        const bubble = document.createElement("message-bubble");
        const isAuthor = msg.recipient === currentRecipient;
        bubble.setAttribute("recipient", msg.recipient);
        bubble.setAttribute("date", msg.date);
        bubble.setAttribute("content", msg.htmlMessage);
        bubble.classList.add("bubble-message");
        if (isAuthor) bubble.classList.add("right");
        if (msg.canDelete && show) bubble.setAttribute("can-delete", "");
        msgsDisplayCtn.appendChild(bubble);
    }

    function scrollToBottom() {
        msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
    }

    msgsDisplayCtn.addEventListener("selected", (e) => {
        const { action, itemId } = e.detail;
        if (action === "delete") {
            fetch(`/message?user=${currentRecipient}`, {
                method: "DELETE",
                body: JSON.stringify({ message: itemId })
            }).then(res => res.json())
                .then(data => socket?.send(JSON.stringify(data)))
                .catch(console.error);
        }
    });
</script>