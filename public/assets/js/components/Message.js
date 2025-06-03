import ActionMenu from "./ActionMenu.js";
import styles from '/assets/css/chatMessage.css' with { type: 'css' }

class Message extends HTMLElement {
  constructor() {
    super();
    this.shadow = this.attachShadow({mode: "open"})
    this.shadow.adoptedStyleSheets = [styles];
    this.pseudo = "";
    this.date = "";
    this.message = "";
    this.hasOptions = false;
    this.options = [];
    this.wrapper = null;
  }

  connectedCallback() {
    this.pseudo = this.getAttribute("pseudo");
    this.date = this.getAttribute("date");
    this.message = this.getAttribute("message");
    this.hasOptions = this.getAttribute("hasOptions");
    this.options = JSON.parse(this.getAttribute("options"));
    this.wrapper = document.createElement("div");
    this.wrapper.setAttribute("class", "message");

    this.wrapper.innerHTML = `
    <div class='msg-ctn'>
        <div class="msg-img"></div>
        <div class="msg-info-ctn">
            <div class="msg-pseudo-date-ctn">
                <p class="msg-pseudo">${this.pseudo}</p>
                <p class="msg-date">${this.date}</p>
            </div>
            <div class="content">${this.message}</div>
        </div>
    </div>
    `;
   
    if (this.hasOptions == "true") {
      const actionMenu = document.createElement("action-menu");
      actionMenu.setAttribute("item-id", this.date);
      actionMenu.setAttribute("options", JSON.stringify(this.options));
      this.wrapper.appendChild(actionMenu);
    }
    this.shadow.innerHTML = ""
    this.shadow.appendChild(this.wrapper)
  }

  disconnectedCallback() {
    if (this.check) {
      this.check.remove();
    }
    if (this.wrapper) {
      this.wrapper.remove();
    }
  }
}

customElements.define("message-box", Message);
