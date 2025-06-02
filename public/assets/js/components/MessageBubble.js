import "/assets/js/emoji-picker-element/picker.js";
import styles from '/assets/css/MessageBubble.css' with { type: 'css' }
import ActionMenu from "./ActionMenu.js";

class MessageBubble extends HTMLElement {
  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: "open" });
    this.shadow.adoptedStyleSheets = [styles];
  }

  connectedCallback() {
    this.render();
  }

  render() {
    const content = this.getAttribute("content") || "";
    const date = this.getAttribute("date") || "";
    const canDelete = this.hasAttribute("can-delete");

    const container = document.createElement("div");
    container.classList.add("bubble");
      
    container.innerHTML = `
      <div class="content">${content}</div>
      <div class="bottom">
          <small>${date}</small>
          ${
            canDelete
              ? `<action-menu item-id="${date}" options='[{"label":"Supprimer","value":"delete"}]'></action-menu>`
              : ""
          }
      </div>
    `;

    this.shadow.innerHTML = "";
    this.shadow.appendChild(container);
  }
}

customElements.define("message-bubble", MessageBubble);
