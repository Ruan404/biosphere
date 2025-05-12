let currentlyOpenMenu = null;

export default class ActionMenu extends HTMLElement {
	constructor() {
		super();
		this.menu = null;
		this.button = null;
		this.visible = false;
		this._rafId = null;
	}

	connectedCallback() {
		const options = JSON.parse(this.getAttribute('options') || '[]');
		if (options.length === 0) return;

		const itemId = this.getAttribute('item-id');

		this.button = document.createElement('button');
		this.button.className = 'action-trigger';
		this.button.innerHTML = `
			<svg width='24' height='24' viewBox='0 0 24 24' fill='currentColor' xmlns='http://www.w3.org/2000/svg'>
				<rect x='11' y='5' width='2' height='2' rx='1'/>
				<rect x='11' y='11' width='2' height='2' rx='1'/>
				<rect x='11' y='17' width='2' height='2' rx='1'/>
			</svg>`;
		this.appendChild(this.button);

		this.menu = document.createElement('div');
		this.menu.className = 'action-menu';
		this.menu.style.position = 'absolute';
		this.menu.style.display = 'none';
		this.appendChild(this.menu);

		options.forEach(opt => {
			const btn = document.createElement('button');
			btn.className = 'menu-btn';
			btn.textContent = opt.label;
			btn.addEventListener('click', () => {
				this.dispatchEvent(new CustomEvent('selected', {
					bubbles: true,
					detail: { action: opt.value, itemId }
				}));
				this.toggle(false);
			});
			this.menu.appendChild(btn);
		});

		this.button.addEventListener('click', (e) => {
			e.stopPropagation();
			if (currentlyOpenMenu && currentlyOpenMenu !== this) {
				currentlyOpenMenu.toggle(false);
			}
			this.toggle(!this.visible);
			if (this.visible) {
				currentlyOpenMenu = this;
			}
		});

		document.addEventListener('click', () => {
			if (this.visible) this.toggle(false);
		});
	}

	toggle(show) {
		if (!this.menu || !this.button) return;
		this.visible = show;
		this.menu.style.display = show ? 'grid' : 'none';

		if (show) {
			document.querySelector("#ctn-display").style.overflowY = "hidden"
			this._startPositionTracking();
		} else {
			document.querySelector("#ctn-display").style.overflowY = "auto"
			this._stopPositionTracking();
		}
	}

	updatePosition() {
		if (!this.menu || !this.button || !this.visible) return;

		const rect = this.button.getBoundingClientRect();
		const menuRect = this.menu.getBoundingClientRect();
		const padding = 8;

		let top = rect.bottom + window.scrollY;
		let left = rect.left + window.scrollX;

		if (top + menuRect.height + padding > window.innerHeight + window.scrollY) {
			top = rect.top + window.scrollY - menuRect.height;
		}
		if (left + menuRect.width + padding > window.innerWidth + window.scrollX) {
			left = rect.right + window.scrollX - menuRect.width;
		}

		this.menu.style.top = `${top}px`;
		this.menu.style.left = `${left}px`;
	}

	_startPositionTracking() {
		this._stopPositionTracking();
		const update = () => {
			if (!this.visible) return;
			this.updatePosition();
			this._rafId = requestAnimationFrame(update);
		};
		this._rafId = requestAnimationFrame(update);
	}

	_stopPositionTracking() {
		if (this._rafId) {
			cancelAnimationFrame(this._rafId);
			this._rafId = null;
		}
	}

	disconnectedCallback() {
		this._stopPositionTracking();
		if (this.menu) this.menu.remove();
		if (currentlyOpenMenu === this) currentlyOpenMenu = null;
	}
}

customElements.define('action-menu', ActionMenu);
