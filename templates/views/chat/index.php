<?php
use App\Helpers\Text;
$style = "chat";
$topics = $data['topics'] ?? [];
$currentTopic = htmlspecialchars($data['currentTopic'] ?? '');
$title = $currentTopic ?? "chat";
?>

<main>
	<div class="container">
		<!--sidebar-->

		<sidebar-tab class="sidebar-ctn">
			<button slot="trigger" class="tab-btn shadow-btn" id="toggle-btn">Topics</button>
			<span slot="current-label"><?= Text::escapeAndRemoveUnderscore($currentTopic) ?></span>

			<?php foreach ($topics as $topic): ?>
				<a slot="menu" class='sidebar-menu-button' data-slug="<?= $topic->name ?>"
					onclick="viewChat(event, '<?= $topic->name ?>')"
					href="#"><?= Text::escapeAndRemoveUnderscore($topic->name) ?></a>
			<?php endforeach ?>
		</sidebar-tab>
		<!--messages-->
		<div class="messages hidden">
			<div class="msgs-display" id="ctn-action-menu">
				<div></div>
			</div>
			<form class="send-msg-form">
				<chat-input></chat-input>
				<input class="primary-btn" type="submit" name="valider" value="envoyer">
			</form>
		</div>
		<div class="no-topic">aucun topic sélectionné</div>
	</div>
</main>
<script type="module" src="/assets/js/components/Message.js"></script>
<script src="/assets/js/components/SideBar.js"></script>
<script type="module" src="/assets/js/components/ChatInput.js"></script>
<script>
	const msgsDisplayCtn = document.querySelector(".msgs-display");
	const messagesCtn = document.querySelector(".messages");
	const form = document.querySelector(".send-msg-form");
	const noTopicCtn = document.querySelector(".no-topic");

	let currentTopic = "<?= $currentTopic ?>";
	let currentUser = null;
	let permissions = [];
	let socket = null;

	function openSocket(topic) {
		if (socket) {
			socket.close();
		}
		socket = new WebSocket(`${WEBSOCKET_URL}/chat/${topic}`);

		socket.onmessage = (event) => {
			const data = JSON.parse(event.data);

			if (data.action === "delete" && Array.isArray(data.messages)) {
				const items = document.querySelectorAll("message-box");
				items.forEach((msgBox) => {
					const itemId = msgBox.getAttribute("date");
					if (data.messages.includes(itemId)) msgBox.remove();
				});
			}

			if (data.htmlMessage) {
				data.options = deriveOptions(data);
				displayMessage(data);
				msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: "smooth" });
			}
		};

		socket.onerror = (err) => {
			console.error("WebSocket error:", err);
		};

		socket.onclose = () => {
			console.warn("WebSocket closed");
		};
	}

	window.addEventListener("DOMContentLoaded", () => {
		if (currentTopic) {
			document.querySelector(`[data-slug=${currentTopic}]`)?.classList.add("current");
			openSocket(currentTopic);
			fetchData(currentTopic);
		}
		history.replaceState({ topic: currentTopic }, `chat ${currentTopic}`, currentTopic ? `/chat/${currentTopic}` : "/chat");
		updateView(currentTopic);
	});

	function updateView(topic) {
		document.title = topic || "chat";
		const hasTopic = Boolean(topic);
		messagesCtn.classList.toggle("hidden", !hasTopic);
		noTopicCtn.classList.toggle("hidden", hasTopic);
	}

	function viewChat(ev, topic) {
		ev.preventDefault();
		if (topic !== currentTopic) {
			document.querySelector(".sidebar-menu-button.current")?.classList.remove("current");
			ev.target.classList.add("current");
			history.pushState({ topic }, `chat ${topic}`, `/chat/${topic}`);
			currentTopic = topic;
			openSocket(topic);
			fetchData(topic);
			updateView(topic);
		}
	}

	window.addEventListener("popstate", (event) => {
		if (event.state) {
			const topic = event.state.topic;
			if (topic !== currentTopic) {
				currentTopic = topic;
				document.querySelector(".sidebar-menu-button.current")?.classList.remove("current");
				if (topic) {
					document.querySelector(`[data-slug=${topic}]`)?.classList.add("current");
					openSocket(topic);
					fetchData(topic);
				} else {
					msgsDisplayCtn.innerHTML = "";
				}
				updateView(topic);
			}
		}
	});

	form.addEventListener("submit", (ev) => {
		ev.preventDefault();
		fetch(`/chat/${currentTopic}`, {
			method: "POST",
			body: new FormData(form),
		})
			.then((response) => response.json())
			.then((data) => {
				if (data.htmlMessage) {
					socket.send(JSON.stringify(data));
					msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: "smooth" });
				}
			})
			.catch(() => console.error("Error submitting the form"))
			.finally(() => form.reset());
	});

	function fetchData(topic) {
		fetch(`/chat/${topic}`, { headers: { Accept: "application/json" } })
			.then((response) => response.json())
			.then((data) => {
				currentUser = data.currentUser;
				permissions = data.permissions;

				msgsDisplayCtn.innerHTML = "";
				data.messages.forEach((chat) => {
					chat.options = deriveOptions(chat);
					displayMessage(chat);
				});

				msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: "smooth" });
				document.querySelector('[slot="current-label"]').textContent = topic;
			})
			.catch((err) => console.error("Error fetching data:", err));
	}

	function deriveOptions(message) {
		const opts = [];
		const isOwner = message.pseudo === currentUser;
		if (permissions.includes("delete_any_chat")) {
			opts.push({ label: "Supprimer", value: "delete" });
		} else if (permissions.includes("delete_own_chat") && isOwner) {
			opts.push({ label: "Supprimer", value: "delete" });
		}
		return opts;
	}

	function deleteMessage(messageId) {
		fetch(`/chat/${currentTopic}`, {
			method: "DELETE",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({ messages: [messageId] }),
		})
			.then((response) => response.json())
			.then((data) => socket.send(JSON.stringify(data)))
			.catch(() => console.error("Error deleting message"));
	}

	function displayMessage(chat, show = true) {
		const hasOptions = show && Array.isArray(chat.options) && chat.options.length > 0;

		const msgBox = document.createElement("message-box");
		msgBox.setAttribute("pseudo", chat.pseudo);
		msgBox.setAttribute("date", chat.date);
		msgBox.setAttribute("message", chat.htmlMessage);
		msgBox.setAttribute("hasOptions", hasOptions);
		msgBox.setAttribute("options", JSON.stringify(chat.options));

		msgsDisplayCtn.appendChild(msgBox);
	}

	msgsDisplayCtn.addEventListener("selected", (e) => {
		const { action, itemId } = e.detail;
		if (action === "delete") {
			deleteMessage(itemId);
		}
	});
</script>

