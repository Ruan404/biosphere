import "/assets/js/emoji-picker-element/picker.js";
import styles from '/assets/css/chatInput.css' with { type: 'css' }


class ChatInput extends HTMLElement {
  constructor() {
    super();
    this.shadow = this.attachShadow({ mode: "closed", delegatesFocus: true });
    this.shadow.adoptedStyleSheets = [styles];
    this._form = null;
    this._handleFormData = this.handleFormData.bind(this);
  }

  connectedCallback() {
    const container = document.createElement("div");
    container.classList.add("container");
    container.innerHTML = `
      <div class="images-preview-ctn">
        <img id="imagePreview" name="image" class="image-preview" />
      </div>
      <div class="input-area">
        <div class="controls">
          <button id="emojiBtn" title="Ajouter un emoji">
            <svg width="24" height="24" viewBox="0 0 24 24" stroke="currentColor" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <circle cx="12" cy="12" r="8.5" fill="none"/>
              <circle cx="8" cy="10.5" r="1.5" stroke="none"/>
              <circle cx="16" cy="10.5" r="1.5" stroke="none"/>
              <path d="M8 15C8 15 9 17 12 17C15 17 16 15 16 15" fill="none" stroke-linecap="round"/>
            </svg>
          </button>
          <button id="imageBtn" title="Ajouter une image">
              <svg width="24" height="24" viewBox="0 0 24 24" stroke="currentColor" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect x="3.5" y="3.5" width="17" height="17" rx="3.5" />
                <path d="M6.88272 14.2346L6.63898 14.722C6.34541 15.3092 6.77237 16 7.42881 16H16.471C17.1921 16 17.6762 15.2599 17.3871 14.5992L15.9962 11.4198C15.3562 9.95712 13.3448 9.79695 12.4815 11.1399L10.5988 14.0686C10.3162 14.5081 9.67563 14.5134 9.38581 14.0787C8.76831 13.1525 7.38057 13.2389 6.88272 14.2346Z" stroke-linecap="round"/>
              </svg>
          </button>
          <input type="file" id="fileInput" name="image" accept="image/*" style="display:none;">
        </div>
        <emoji-picker locale="fr" i18n="fr" data-source="/assets/js/emoji-picker-element/data.json" style="display: none;"></emoji-picker>
        <textarea name="message" placeholder="Écrivez votre message..."></textarea>
      </div>
    `;

    this.shadow.innerHTML = "";
    this.shadow.appendChild(container);

    this.textarea = this.shadow.querySelector("textarea");
    this.emojiBtn = this.shadow.querySelector("#emojiBtn");
    this.imageBtn = this.shadow.querySelector("#imageBtn");
    this.fileInput = this.shadow.querySelector("#fileInput");
    this.emojiPicker = this.shadow.querySelector("emoji-picker");
    this.imagePreview = this.shadow.querySelector("#imagePreview");

    this.emojiBtn.addEventListener("click", (e) => {
      e.preventDefault();
      this.emojiPicker.style.display =
        this.emojiPicker.style.display === "none" ? "block" : "none";
    });

    this.emojiPicker.addEventListener("emoji-click", (event) => {
      const emoji = event.detail.unicode;
      this.insertAtCursor(emoji);
      this.emojiPicker.style.display = "none";
    });

    document.addEventListener("click", (e) => {
      if (!this.contains(e.target) && !this.shadow.contains(e.target)) {
        this.emojiPicker.style.display = "none";
      }
    });

    this.imageBtn.addEventListener("click", (e) => {
      e.preventDefault();
      this.fileInput.click();
    });

    this.fileInput.addEventListener("change", () => {
      const file = this.fileInput.files[0];
      if (!file) return;

      const fileMb = file.size / 1024 ** 2;
      if (fileMb >= 2) {
        alert("Veuillez sélectionner un fichier de moins de 2 Mo.");
        this.fileInput.value = "";
      } else {
        const reader = new FileReader();
        reader.onload = () => {
          this.imagePreview.src = reader.result;
          this.imagePreview.style.display = "block";
        };
        reader.readAsDataURL(file);
      }
    });

    this._form = this.findContainingForm();
    if (this._form) {
      this._form.addEventListener("formdata", this._handleFormData);
      this._form.addEventListener("submit", (e) => {
        if (!this.textarea.value.trim() && !this.fileInput.files.length) {
          e.preventDefault();
          alert("Veuillez écrire un message ou ajouter une image.");
          return;
        }
        setTimeout(() => this.reset(), 0);
      });
    }
  }

  handleFormData({ formData }) {
    if (!this.textarea.disabled) {
      formData.append(this.textarea.name, this.textarea.value);
    }
    if (!this.fileInput.disabled && this.fileInput.files.length) {
      formData.append(this.fileInput.name, this.fileInput.files[0]);
    }
  }

  insertAtCursor(emoji) {
    const textarea = this.textarea;
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    textarea.value = text.slice(0, start) + emoji + text.slice(end);
    textarea.selectionStart = textarea.selectionEnd = start + emoji.length;
    textarea.focus();
  }

  reset() {
    this.textarea.value = "";
    this.fileInput.value = "";
    this.imagePreview.src = "";
    this.imagePreview.style.display = "none";
  }

  findContainingForm() {
    const root = this.getRootNode();
    const forms = Array.from(root.querySelectorAll("form"));
    return forms.find((form) => form.contains(this)) || null;
  }
}

customElements.define("chat-input", ChatInput);
