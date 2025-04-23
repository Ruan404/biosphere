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
    <!-- Bouton hamburger pour ouvrir la liste des utilisateurs -->
    <div class="hamburger-menu" onclick="toggleUserList()">☰</div>
</header>

<div class="container">
    <!-- Liste des utilisateurs -->
    <div class="user-list" id="userList">
        <h2>Utilisateurs disponibles</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <a href="?user_id=<?= $user['id'] ?>"><?= htmlspecialchars($user['pseudo']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Conteneur de conversation -->
    <div class="conversation-container">
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
                            <message-bubble
                                content="<?= htmlspecialchars($message['message']) ?>"
                                date="<?= $message['date'] ?>"
                                message-id="<?= $message['id'] ?>"
                                <?= $message['id_auteur'] === $currentUserId || $_SESSION['role'] === 'admin' ? 'can-delete' : '' ?>
                                <?= $message['id_auteur'] === $currentUserId ? 'align="right"' : '' ?>
                            ></message-bubble>
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
        <?php else: ?>
            <div class="no-user">Aucun utilisateur sélectionné</div>
        <?php endif; ?>
    </div>
</div>

<script>
// Fonction pour basculer l'affichage de la liste des utilisateurs
function toggleUserList() {
    const userList = document.getElementById('userList');
    userList.classList.toggle('show');
}
</script>

<script>
  // Lorsque le bouton "Close" est cliqué, on cache la liste des utilisateurs
  document.querySelector('.close-btn').addEventListener('click', function() {
    document.querySelector('.user-list').classList.add('closed');
  });
</script>


<script>
class MessageBubble extends HTMLElement {
    constructor() {
        super();
        this.shadow = this.attachShadow({ mode: 'open' });
    }

    connectedCallback() {
        this.render();
    }

    render() {
        const content = this.getAttribute('content') || '';
        const date = this.getAttribute('date') || '';
        const messageId = this.getAttribute('message-id');
        const userId = new URLSearchParams(window.location.search).get('user_id');
        const canDelete = this.hasAttribute('can-delete');

        const container = document.createElement('div');
        container.classList.add('bubble');

        container.innerHTML = `
            <p class="content">${content}</p>
            <div class="bottom">
                <small>${date}</small>
                ${canDelete ? `
                    <div class="menu-wrapper">
                        <button class="menu-btn" title="Options">⋯</button>
                        <div class="options-menu" hidden>
                            <button class="delete-option">Supprimer</button>
                        </div>
                    </div>` : ''}
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            .bubble {
                position: relative;
                max-width: 60%;
                margin: 0.5em 0;
                padding: 0.75em 1em;
                background: #f0f0f0;
                border-radius: 1em;
                font-family: Arial, sans-serif;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                word-wrap: break-word;
            }
            :host([align="right"]) .bubble {
                background: #d1e7dd;
                margin-left: auto;
                text-align: right;
            }
            .bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-top: 0.5em;
            }
            .menu-wrapper {
                position: relative;
            }
            .menu-btn {
                background: none;
                border: none;
                font-size: 1.2em;
                cursor: pointer;
                padding: 0;
            }
            .options-menu {
                position: absolute;
                right: 0;
                top: -45px;
                background: white;
                border: 1px solid #ccc;
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                z-index: 10;
            }
            .options-menu button {
                background: none;
                border: none;
                padding: 8px 12px;
                cursor: pointer;
                width: 100%;
                text-align: left;
            }
            .options-menu button:hover {
                background-color: #eee;
            }
            small {
                font-size: 0.75em;
                color: #888;
            }
        `;

        this.shadow.innerHTML = '';
        this.shadow.appendChild(style);
        this.shadow.appendChild(container);

        if (canDelete) {
            const menuBtn = container.querySelector('.menu-btn');
            const menu = container.querySelector('.options-menu');
            const deleteBtn = container.querySelector('.delete-option');

            menuBtn.addEventListener('click', function() {
                menu.hidden = !menu.hidden;
            });

            deleteBtn.addEventListener('click', function() {
                fetch(`/message/delete/${messageId}`, {
                    method: 'DELETE',
                }).then(() => location.reload());
            });
        }
    }
}

customElements.define('message-bubble', MessageBubble);
</script>

</body>
</html>
