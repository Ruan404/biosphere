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
        *{
            margin: 0;
            padding: 0;
        }
        .bubble {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0.5rem 0.75rem;
            gap: 0.625rem;
            word-wrap: break-word;
            grid-column: 1 / span 8;
            border-radius: 0.5rem;
            background: rgb(var(--bg-1));
            font-size: 0.875rem;
            max-width: fit-content;
        }
        :host(.bubble.right) .bubble{
            grid-column: span 8 / -1;
            background: rgb(var(--bg-4));
        }
        .bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            column-gap: 1rem
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
            color: inherit;
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
            font-size: 0.625em;
            color: rgb(var(--fg-1), 0.6);
        }

        @media (max-width: 768px) { 
            .bubble{
                grid-column: 1 / span 11;
            }

            :host(.bubble.right) .bubble{ 
                grid-column: span 11 / -1;
            }
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