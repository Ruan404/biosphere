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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie</title>
    <link rel="stylesheet" href="/assets/css/message.css">
</head>
<body>

<div class="container">
    <!-- Nouveau bouton pour mobile -->
    <div class="tab-users mobile-only">
        <button class="tab-btn shadow-btn" onclick="showUserList()">Contacts</button>
        <?php if (isset($recipient)): ?>
            <h3 class="current-contact"><?= htmlspecialchars($recipient['pseudo']) ?></h3>
        <?php endif ?>
    </div>

    <div class="user-list" id="userList">
        <!-- Bouton de fermeture pour mobile -->
        <!-- <button class="close-btn icon-btn mobile-only" onclick="hideUserList()" aria-label="Fermer la liste">
            <svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M20.3536 4.35355C20.5488 4.15829 20.5488 3.84171 20.3536 3.64645C20.1583 3.45118 19.8417 3.45118 19.6464 3.64645L12 11.2929L4.35355 3.64645C4.15829 3.45118 3.84171 3.45118 3.64645 3.64645C3.45118 3.84171 3.45118 4.15829 3.64645 4.35355L11.2929 12L3.64645 19.6464C3.45118 19.8417 3.45118 20.1583 3.64645 20.3536C3.84171 20.5488 4.15829 20.5488 4.35355 20.3536L12 12.7071L19.6464 20.3536C19.8417 20.5488 20.1583 20.5488 20.3536 20.3536C20.5488 20.1583 20.5488 19.8417 20.3536 19.6464L12.7071 12L20.3536 4.35355Z"/>
            </svg>
        </button> -->
        
        <h2>Utilisateurs disponibles</h2>
        <ul>
            <?php foreach ($users as $user): ?>
                <li>
                    <a href="?user_id=<?= $user['id'] ?>"><?= htmlspecialchars($user['pseudo']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="conversation-container">
        <?php if (isset($_GET['user_id'])): ?>
            <?php
                $recipientId = (int) $_GET['user_id'];
                $messages = $messageService->getMessages($recipientId);
                $recipient = $messageService->getUserById($recipientId);
            ?>
            <?php if ($recipient): ?>
                <div class="conversation full-page">
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
                            
                    <form method="POST" action="/message/<?= $recipientId ?>" class="send-message-form">
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
                max-width: 40%;
                margin: 15px 0;
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

            menuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                menu.hidden = !menu.hidden;
            });

            document.addEventListener('click', () => {
                menu.hidden = true;
            });

            deleteBtn.addEventListener('click', () => {
                if (confirm("Supprimer ce message ?")) {
                    fetch(`/message/${userId}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ message_id: messageId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            this.remove();
                        } else {
                            alert("Erreur : " + data.message);
                        }
                    })
                    .catch(() => alert("Erreur lors de la suppression."));
                }
            });
        }
    }
}

customElements.define('message-bubble', MessageBubble);

// Gestion de l'affichage mobile
function showUserList() {
    document.getElementById('userList').classList.add('show');
    document.body.classList.add('no-scroll');
}

function hideUserList() {
    document.getElementById('userList').classList.remove('show');
    document.body.classList.remove('no-scroll');
}

// Fermer la liste si on clique à l'extérieur
document.addEventListener('click', function(event) {
    const userList = document.getElementById('userList');
    if (event.target.closest('.tab-btn') || event.target.closest('.user-list')) return;
    if (userList.classList.contains('show')) {
        hideUserList();
    }
});

// Fermer automatiquement quand on sélectionne un utilisateur
document.querySelectorAll('.user-list a').forEach(link => {
    link.addEventListener('click', () => {
        hideUserList();
    });
});
</script>

</body>
</html>