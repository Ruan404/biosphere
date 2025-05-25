import ActionMenu from "./ActionMenu.js";

class Message extends HTMLElement {
  constructor() {
    super();
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
            ${this.message}
        </div>
    </div>
    `;
   
    if (this.hasOptions) {
      const actionMenu = document.createElement("action-menu");
      actionMenu.setAttribute("item-id", this.date);
      actionMenu.setAttribute("options", JSON.stringify(this.options));
      this.wrapper.appendChild(actionMenu);
    }
    this.innerHTML = this.wrapper.outerHTML

  }

  disconnectedCallback() {
    if (this.wrapper) {
      this.wrapper.remove();
    }
  }
}

customElements.define("message-box", Message);
