export default class ActionMenu extends HTMLElement {
	constructor() {
		super();
		this.menu = null;
		this.button = null;
		this.visible = false;
	}

	connectedCallback() {
		const options = JSON.parse(this.getAttribute('options') || '[]');

		if (options.length === 0) return;

		this.button = document.createElement('button');
		this.button.innerHTML = ` 
			<svg width='24' height='24' viewBox='0 0 24 24' fill='currentColor' xmlns='http://www.w3.org/2000/svg'>
                <rect x='11' y='5' width='2' height='2' rx='1'/>
                <rect x='11' y='11' width='2' height='2' rx='1'/>
                <rect x='11' y='17' width='2' height='2' rx='1'/>
            </svg>`;
			
		this.button.setAttribute('class', 'action-trigger');
		this.appendChild(this.button);
		this.menu = document.createElement('div');
		this.menu.setAttribute('class', 'action-menu');

		const itemId = this.getAttribute('item-id');

		options.forEach(opt => {
			const btn = document.createElement('button');
			btn.textContent = opt.label;
			btn.setAttribute('class', 'menu-btn');
			btn.addEventListener('click', () => {
				this.dispatchEvent(new CustomEvent('selected', {
					bubbles: true,
					detail: { action: opt.value, itemId }
				}));
				this.toggle(false);
			});
			this.menu.appendChild(btn);
		});

		document.body.appendChild(this.menu);

		this.button.addEventListener('click', (e) => {
			e.stopPropagation();
			this.toggle(!this.visible);
		});

		document.addEventListener('click', () => {
			this.toggle(false);
		});
	}

	toggle(show) {
		if (!this.menu || !this.button) return;
		this.visible = show;
		this.menu.style.display = show ? 'grid' : 'none';
		if (show) {
			const rect = this.button.getBoundingClientRect();
			const menuRect = this.menu.getBoundingClientRect();
			const padding = 8;

			let top = rect.bottom + window.scrollY;
			let left = rect.left + window.scrollX;

			// Adjust vertically if it overflows
			if (top + menuRect.height + padding > window.innerHeight + window.scrollY) {
				top = rect.top + window.scrollY - menuRect.height;
			}

			// Adjust horizontally if it overflows
			if (left + menuRect.width + padding > window.innerWidth + window.scrollX) {
				left = rect.right + window.scrollX - menuRect.width;
			}

			this.menu.style.top = `${top}px`;
			this.menu.style.left = `${left}px`;
		}
	}

	disconnectedCallback() {
		if (this.menu) {
			this.menu.remove();
		}
	}
}

customElements.define('action-menu', ActionMenu);
