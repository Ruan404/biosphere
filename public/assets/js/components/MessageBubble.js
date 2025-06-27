import "/assets/js/emoji-picker-element/picker.js";
import styles from '/assets/css/messageBubble.css' with { type: 'css' }
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
    const options = this.getAttribute("options");

    let menuHTML = "";
    if (options) {
      try {
        const opts = JSON.parse(options);
        if (Array.isArray(opts) && opts.length > 0) {
          menuHTML = `<action-menu item-id="${date}" options='${JSON.stringify(opts)}'></action-menu>`;
        }
      } catch (err) {
        console.log;
      }
    }

    const container = document.createElement("div");
    container.classList.add("bubble");

    container.innerHTML = `
      <div class="content">${content}</div>
      <div class="bottom">
        <small>${date}</small>
        ${menuHTML}
      </div>
    `;

    this.shadow.innerHTML = "";
    this.shadow.appendChild(container);
  }
}

customElements.define("message-bubble", MessageBubble);
