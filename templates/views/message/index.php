<?php
$style = "message";

$users = $data["users"] ?? [];
$recipient = $data["recipient"] ?? null;
$recipientPseudo = htmlspecialchars($recipient["pseudo"] ?? "");
$recipientImage = htmlspecialchars($recipient["image"] ?? "");
?>
<main>
	<div class="container">
		<!-- Sidebar -->
		<sidebar-tab class="sidebar-ctn" id="contact-bar">
			<button slot="trigger" class="tab-btn shadow-btn" id="toggle-tab">Contacts</button>
			<h2 class="sidebar-title" slot="title">Utilisateurs disponibles</h2>
			<?php foreach ($users as $user): ?>

				<a slot="menu" class="sidebar-menu-button" data-slug="<?= $user->pseudo ?>"
					onclick="viewConversation(event, '<?= $user->pseudo ?>')" href="#">
					<img class="user-profil-img" src="<?= htmlspecialchars($user->image) ?>"
						alt="Avatar de <?= htmlspecialchars($user->pseudo) ?>">
					<span class="user-name"><?= htmlspecialchars($user->pseudo) ?></span>
				</a>
			<?php endforeach; ?>
		</sidebar-tab>

		<!-- Message Container -->
		<div class="conversation-container">
			<div class="conversations <?= $recipient ? '' : 'hidden' ?>">
				<div class="title">
					<img class="user-profil-img" src="<?= $recipientImage ?>" alt="Avatar de <?= $recipientPseudo ?>">
					<h2>Conversation avec <?= $recipientPseudo ?></h2>
				</div>
				<div class="messages" id="ctn-action-menu"></div>
				<form id="form" method="POST" class="send-message-form">
					<chat-input></chat-input>
					<button class="primary-btn" type="submit">Envoyer</button>
				</form>
			</div>
			<div class="no-user">Aucun utilisateur sélectionné</div>
		</div>
	</div>
</main>

<script src="/assets/js/components/SideBar.js"></script>
<script type="module" src="/assets/js/components/MessageBubble.js"></script>
<script type="module" src="/assets/js/components/ChatInput.js"></script>

<script>
	const form = document.getElementById('form');
	const messagesCtn = document.querySelector('.conversations');
	const msgsDisplayCtn = document.querySelector('.messages');
	const noUserCtn = document.querySelector('.no-user');

	let currentRecipient = "<?= $recipientPseudo ?>";
	const currentUser = "<?= htmlspecialchars($_SESSION["username"] ?? "") ?>";

	let socket;

	// Fetch JWT token from server API
	async function getToken() {
		const res = await fetch('/api/token', { credentials: 'include' });
		if (!res.ok) return;
		const data = await res.json();
		return data.token;
	}

	// Connect WebSocket with fresh token
	async function connectWebSocket() {
		try {
			const token = await getToken();
			socket = new WebSocket(`${WEBSOCKET_URL}/message?token=${token}`);

			socket.onmessage = handleMessage;

			socket.onclose = async (event) => {
				// Try reconnecting on close (e.g., token expired)
				console.log("Socket closed, reconnecting...");
				setTimeout(connectWebSocket, 1000);
			};

		} catch (err) {
			console.error("WebSocket connection error:", err);
		}
	}

	window.addEventListener("DOMContentLoaded", () => {
		if (currentRecipient) {
			document.querySelector(`[data-slug="${currentRecipient}"]`)?.classList.add("current");
			fetchData(currentRecipient);
		}
		updateView(currentRecipient);
		history.replaceState({ user: currentRecipient }, `Messages ${currentRecipient}`, currentRecipient ? `/message?user=${currentRecipient}` : "/message");
		connectWebSocket();
	});

	function updateView(user) {
		document.title = user || "messages";
		const hasUser = Boolean(user);
		messagesCtn.classList.toggle("hidden", !hasUser);
		noUserCtn?.classList.toggle("hidden", hasUser);
	}

	function viewConversation(ev, user) {
		ev.preventDefault();
		if (user !== currentRecipient) {
			document.querySelector(".sidebar-menu-button.current")?.classList.remove("current");
			ev.target.classList.add("current");

			currentRecipient = user;
			history.pushState({ user }, `Messages ${user}`, `/message?user=${user}`);
			fetchData(user);
			updateView(user);
		}
	}

	window.addEventListener("popstate", (e) => {
		const user = e.state?.user || "";
		if (user !== currentRecipient) {
			currentRecipient = user;
			document.querySelector(".sidebar-menu-button.current")?.classList.remove("current");
			document.querySelector(`[data-slug="${user}"]`)?.classList.add("current");
			if (user) fetchData(user);
			else msgsDisplayCtn.innerHTML = "";
			updateView(user);
		}
	});

	form.addEventListener("submit", (e) => {
		e.preventDefault();
		const formData = new FormData(form);
		fetch(`/message?user=${currentRecipient}`, {
			method: 'POST',
			body: formData
		})
			.then(res => res.json())
			.then(data => {
				if (data.htmlMessage) socket?.send(JSON.stringify(data))
			})
			.catch(() => console.log)
			.finally(() => form.reset());
	});

	function handleMessage(event) {
		const data = JSON.parse(event.data);

		if (data.action === "delete" && Array.isArray(data.messages)) {
			data.messages.forEach(id => {
				document.querySelectorAll('message-bubble').forEach(b => {
					if (b.getAttribute('date') === id) b.remove();
				});
			});
		}

		if (data.action === "add") {
			const sender = data?.sender;
			const recipient = data?.recipient;

			if ((sender === currentRecipient && recipient === currentUser) || (recipient === currentRecipient && sender === currentUser)) {
				data.options = deriveOptions(data);
				displayMessage(data);
				scrollToBottom();
			}
		}
	}

	function fetchData(user) {
		fetch(`/message?user=${user}`, { headers: { 'Accept': 'application/json' } })
			.then(res => res.json())
			.then(data => {
				// Clear old messages
				msgsDisplayCtn.innerHTML = "";

				// Update conversation title and avatar
				if (data.recipient) {
					const titleEl = document.querySelector('.conversation-container .title h2');
					const imgEl = document.querySelector('.conversation-container .title img');

					titleEl.textContent = `Conversation avec ${data.recipient.pseudo}`;
					imgEl.setAttribute('src', data.recipient.image);
					imgEl.setAttribute('alt', `Avatar de ${data.recipient.pseudo}`);
				}

				// Render messages
				data.messages.forEach(msg => {
					msg.options = deriveOptions(msg);
					displayMessage(msg);
				});

				scrollToBottom();
			})
			.catch(err => {
				console.log("Error fetching messages:", err);
			});
	}

	function deriveOptions(message) {
		const opts = [];
		const isOwner = message.sender === currentUser;

		if (isOwner) {
			opts.push({ label: "Supprimer", value: "delete" });
		}
		return opts;
	}

	function displayMessage(msg, show = true) {
		const bubble = document.createElement("message-bubble");
		bubble.setAttribute("content", msg.htmlMessage);
		bubble.setAttribute("date", msg.date);
		bubble.setAttribute("options", JSON.stringify(msg.options || []));
		bubble.setAttribute("class", "bubble-message")
		if (msg.recipient === currentRecipient) {
			bubble.classList.add("right");
		}
		msgsDisplayCtn.appendChild(bubble);
	}

	function scrollToBottom() {
		msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
	}

	msgsDisplayCtn.addEventListener("selected", (e) => {
		const { action, itemId } = e.detail;
		if (action === "delete") {
			fetch(`/message?user=${currentRecipient}`, {
				method: "DELETE",
				body: JSON.stringify({ message: itemId })
			})
				.then(res => res.json())
				.then(data => socket?.send(JSON.stringify(data)))
				.catch(() => console.log);
		}
	});
</script>