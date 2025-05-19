import ActionMenu from "./ActionMenu.js";

class MessageBubble extends HTMLElement {
  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: "open" });
  }

  connectedCallback() {
    this.render();
  }

  render() {
    const content = this.getAttribute("content") || "";
    const date = this.getAttribute("date") || "";
    const canDelete = this.hasAttribute("can-delete");
    const recipient = this.getAttribute("recipient");

    const container = document.createElement("div");
    container.classList.add("bubble");

    container.innerHTML = `
      <p class="content">${content}</p>
      <div class="bottom">
          <small>${date}</small>
          ${
            canDelete
              ? `<action-menu item-id="${date}" options='[{"label":"Supprimer","value":"delete"}]'></action-menu>`
              : ""
          }
      </div>
    `;

    const style = document.createElement("style");
    style.textContent = `
      * {
        margin: 0;
        padding: 0;
      }
      .bubble {
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
      :host(.bubble-message.right) .bubble {
        grid-column: span 8 / -1;
        background: rgb(var(--bg-4));
      }
      .bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        column-gap: 1rem;
      }
      small {
        font-size: 0.625em;
        color: rgb(var(--fg-1), 0.6);
      }

      :host(.bubble-message) {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        column-gap: 0.875rem;
      }

      :host(.bubble-message.right) {
        justify-items: end;
      }

      @media (max-width: 768px) {
        .bubble {
          grid-column: 1 / span 11;
        }
        :host(.bubble-message.right) .bubble {
          grid-column: span 11 / -1;
        }
      }
    `;

    this.shadow.innerHTML = "";
    this.shadow.appendChild(style);
    this.shadow.appendChild(container);

    // Listen to action-menu event
    if (canDelete) {
      const actionMenu = container.querySelector("action-menu");
      actionMenu.addEventListener("selected", (e) => {
        const { action } = e.detail;
        if (action === "delete") {
          if (confirm("Supprimer ce message ?")) {
            fetch(`/message?user=${recipient}`, {
              method: "DELETE",
              headers: {
                "Content-Type": "application/json",
              },
              body: JSON.stringify({ message: date }),
            })
              .then((res) => res.json())
              .then((data) => {
                if (data.success === true) {
                  this.remove();
                } else {
                  alert("Erreur : " + data.message);
                }
              })
              .catch(() => alert("Erreur lors de la suppression."));
          }
        }
      });
    }
  }
}

customElements.define("message-bubble", MessageBubble);
