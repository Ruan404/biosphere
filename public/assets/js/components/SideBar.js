class SidebarTab extends HTMLElement {
  constructor() {
    super();
    this.attachShadow({ mode: "open" });
  }

  connectedCallback() {
    const style = document.createElement("style");
    style.textContent = `
      * {
        box-sizing: border-box;
        margin: 0;
      }
      h3 {
        font-size: 1.125rem;
      }
      .menu-ctn,
      .sidebar-menu-ctn {
        display: flex;
        flex-direction: column;
      }
      .sidebar {
        height: 100%;
        overflow-y: auto;
      }
      .sidebar-menu-ctn {
        row-gap: 1rem;
        padding: 1rem;
        max-width: 360px;
        transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
        background: rgb(var(--bg-1));
      }
      .menu-ctn {
        row-gap: 0.875rem;
        font-size: 0.875rem;
      }
      .sidebar-menu-ctn.show {
        transform: translateX(0);
      }
      ::slotted(.sidebar-menu-button) {
        padding: 0.5rem 0.625rem !important;
        border-radius: 0.625rem;
        text-align: left;
      }
      ::slotted(.sidebar-menu-button:hover) {
        background: rgb(var(--bg-3), 0.14) !important;
      }
      ::slotted(.sidebar-menu-button.current) {
        background: rgb(var(--bg-3)) !important;
        pointer-events: none;
        color: rgb(var(--fg-2)) !important;
      }
      .sidebar-tab {
        row-gap: 0.5rem;
        display: none;
      }
      .sidebar-current-tab::first-letter {
        text-transform: capitalize;
      }
      ::slotted(.tab-btn) {
        width: fit-content;
      }
      .close-btn {
        display: none;
        place-self: end;
      }
      .icon-btn {
        border-radius: 10rem;
        padding: 0.5rem;
        color: rgb(var(--fg-1));
      }
      .icon-btn:hover {
        background-color: rgb(var(--bg-3), 0.14);
      }
      svg {
        display: flex;
        fill: currentColor;
      }
      button {
        cursor: pointer;
        transition: all 300ms ease-in;
        appearance: none;
        border: none;
        background: transparent;
        color: inherit;
        height: fit-content;
      }

      @media (max-width: 768px) {
        .sidebar-menu-ctn {
          position: absolute;
          left: 0;
          top: 0;
          transform: translateX(-100%);
          z-index: 100;
          width: calc(100% - 2.5rem);
          height: 100%;
        }
        .close-btn {
          display: inline-block;
        }
        .sidebar-tab {
          display: grid;
        }
      }
    `;
    this.shadowRoot.appendChild(style);

    const wrapper = document.createElement("div");
    wrapper.setAttribute("class", "sidebar");
    wrapper.innerHTML = `
      <div class="sidebar-tab">
        <slot name="trigger" id="toggle-btn"></slot>
        <h3 class="sidebar-current-tab">
          <slot name="current-label"></slot>
        </h3>
      </div>
      <div class="sidebar-menu-ctn" id="menu-ctn">
        <button class="close-btn icon-btn" id="close-btn" aria-label="close button">
          <svg width="24" height="24" viewBox="0 0 24 24">
            <path
              d="M20.3536 4.35355C20.5488 4.15829 20.5488 3.84171 20.3536 3.64645C20.1583 3.45118 19.8417 3.45118 19.6464 3.64645L12 11.2929L4.35355 3.64645C4.15829 3.45118 3.84171 3.45118 3.64645 3.64645C3.45118 3.84171 3.45118 4.15829 3.64645 4.35355L11.2929 12L3.64645 19.6464C3.45118 19.8417 3.45118 20.1583 3.64645 20.3536C3.84171 20.5488 4.15829 20.5488 4.35355 20.3536L12 12.7071L19.6464 20.3536C19.8417 20.5488 20.1583 20.5488 20.3536 20.3536C20.5488 20.1583 20.5488 19.8417 20.3536 19.6464L12.7071 12L20.3536 4.35355Z" />
          </svg>
        </button>
        <div class="sidebar-menu">
          <slot name="title"></slot>
          <div class="menu-ctn">
            <slot name="menu"></slot>
          </div>
        </div>
      </div>
    `;
    this.shadowRoot.appendChild(wrapper);

    const toggleBtn = wrapper.querySelector("#toggle-btn");
    const closeBtn = wrapper.querySelector("#close-btn");
    const menuCtn = wrapper.querySelector("#menu-ctn");
    this._menuCtn = menuCtn;

    toggleBtn.addEventListener("click", () => this.open());
    closeBtn.addEventListener("click", () => this.close());

    // Close on mask click
    document.body.addEventListener("click", (ev) => {
      if (ev.target.classList.contains("black-mask")) {
        this.close();
      }
    });

    // Close sidebar on tab click for mobile
    const menuSlot = wrapper.querySelector('slot[name="menu"]');
    menuSlot.addEventListener("slotchange", () => {
      const tabButtons = menuSlot.assignedElements().filter(el =>
        el.classList.contains("sidebar-menu-button")
      );
      tabButtons.forEach(button => {
        button.addEventListener("click", () => {
          if (window.innerWidth <= 768) {
            this.close();
          }
        });
      });
    });
  }

  open() {
    this._menuCtn?.classList.add("show");
    document.body.classList.add("black-mask");
  }

  close() {
    this._menuCtn?.classList.remove("show");
    document.body.classList.remove("black-mask");
  }

  toggle() {
    if (this._menuCtn?.classList.contains("show")) {
      this.close();
    } else {
      this.open();
    }
  }
}

customElements.define("sidebar-tab", SidebarTab);
