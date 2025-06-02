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
	const msgsDisplayCtn = document.querySelector(".msgs-display")
	const messagesCtn = document.querySelector(".messages")
	const form = document.querySelector(".send-msg-form")
	var currentTopic = "<?= $currentTopic ?>";
	const socket = new WebSocket(`${WEBSOCKET_URL}/chat/${currentTopic}`);
	const noTopicCtn = document.querySelector(".no-topic")

	/**
	 * met à jour la vue
	 * @param {string} topic
	 */
	function updateView(topic) {
		document.title = topic || "chat";
		const hasTopic = Boolean(topic);
		messagesCtn.classList.toggle("hidden", !hasTopic);
		noTopicCtn.classList.toggle("hidden", hasTopic);
	}

	//au chargement de la page
	window.addEventListener("DOMContentLoaded", () => {
		let topic = currentTopic;
		if (currentTopic) {
			document.querySelector(`[data-slug=${topic}]`).classList.add("current")
			fetchData(currentTopic)
		}
		history.pushState({ topic }, `chat ${topic}`, topic ? `/chat/${topic}` : "/chat");		
		updateView(topic)
	})

	function viewChat(ev, topic) {
		ev.preventDefault();

		if (topic != currentTopic) {
			document.querySelector(".sidebar-menu-button.current")?.classList.remove("current")

			ev.target.classList.add("current")
			history.pushState({ topic }, `chat ${topic}`, `/chat/${topic}`)
			fetchData(topic)
			updateView(topic)
		}
	}

	// This event listener will capture when the user navigates forward or backward
	window.addEventListener('popstate', function (event) {
		if (event.state) {
			const topic = event.state.topic;

			if (topic !== currentTopic) {
				currentTopic = topic;
				document.querySelector(".sidebar-menu-button.current")?.classList?.remove("current");
				if (topic) {
					document.querySelector(`[data-slug=${topic}]`)?.classList?.add("current");
					fetchData(topic);
				}
				else msgsDisplayCtn.innerHTML = ""
				updateView(topic)
			}
		} else console.log('No state associated with this entry');
	});


	form.addEventListener("submit", (ev) => {
		ev.preventDefault();
		fetch(`/chat/${currentTopic}`, {
			method: 'POST',
			body: new FormData(form)
		})
			.then(response => response.json())
			.then(data => {
				if (data.htmlMessage) {
					socket.send(JSON.stringify(data))
					displayMessage(data)
					msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
				}
			})
			.catch(error => console.error("Error submitting the form"))
			.finally(() => {
				form.reset()
			});
	})

	// When a message is received from the WebSocket server
	socket.onmessage = function (event) {
		const data = JSON.parse(event.data);
		if (data.action === "delete" && data.messages) {
			const items = document.querySelectorAll('message-box');
			items.forEach((msgBox) => {
				const itemId = msgBox.getAttribute('date');
				if (data.messages.includes(itemId)) msgBox.remove();
			});
		}
		// New chat event
		if (data.htmlMessage && data.topic === currentTopic) {
			displayMessage(data, false)
			msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
		}
	}

	function fetchData(topic) {
		fetch(`/chat/${topic}`, {
			headers: { 'Accept': 'application/json' }
		})
			.then((response) => response.json())
			.then((data) => {
				msgsDisplayCtn.innerHTML = "";
				data?.messages.forEach(chat => displayMessage(chat));

				msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
				currentTopic = topic;
				document.querySelector('[slot="current-label"]').textContent = currentTopic
			})
			.catch((err) => console.error("Error fetching data"));
	}

	function deleteMessage(message) {
		fetch(`/chat/${currentTopic}`, {
			method: 'DELETE',
			body: JSON.stringify({ "messages": [message] })
		})
			.then(response => response.json())
			.then(data => socket.send(JSON.stringify(data)))
			.catch(error => console.error("Error submitting the form"));
	}

	function displayMessage(chat, show = true) {
		const hasOptions = show && Array.isArray(chat?.options) && chat?.options?.length > 0;

		const msgBox = document.createElement('message-box');

		msgBox.setAttribute('pseudo', chat.pseudo)
		msgBox.setAttribute('date', chat.date)
		msgBox.setAttribute('message', chat.htmlMessage)
		msgBox.setAttribute('hasOptions', hasOptions)
		msgBox.setAttribute('options', JSON.stringify(chat.options))

		msgsDisplayCtn.appendChild(msgBox)
	}
</script>
<script>
	msgsDisplayCtn.addEventListener("selected", (e) => {
		const { action, itemId } = e.detail;
		if (action === "delete") {
			deleteMessage(itemId);
		}
	});
</script>