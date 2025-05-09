<?php
use App\Helpers\Text;
$style = "chat";
$topics = $data['topics'] ?? [];
$currentTopic = htmlspecialchars($data['currentTopic'] ?? '');

?>

<main>
	<div class="container">
		<!--sidebar-->

		<sidebar-tab class="sidebar-ctn">
			<button slot="trigger" class="tab-btn shadow-btn" id="toggle-btn">Topics</button>
			<span slot="current-label"><?= Text::removeUnderscore($currentTopic) ?></span>

			<?php foreach ($topics as $topic): ?>
				<a slot="menu" class='sidebar-menu-button' data-slug="<?= $topic->name ?>"
					onclick="viewChat(event, '<?= $topic->name ?>')"
					href="#"><?= Text::removeUnderscore($topic->name) ?></a>
			<?php endforeach ?>
		</sidebar-tab>
		<!--messages-->
		<div class="messages">
			<div class="msgs-display">
				<div></div>
			</div>
			<form class="send-msg-form">
				<textarea name="message" required autocomplete="off" placeholder="Entrez votre message"></textarea>
				<input class="primary-btn" type="submit" name="valider" value="envoyer">
			</form>
		</div>
	</div>
</main>
<script type="module" src="/assets/js/components/Message.js"></script>
<script src="/assets/js/components/SideBar.js"></script>

<script>
	const msgsDisplayCtn = document.querySelector(".msgs-display")
	const form = document.querySelector(".send-msg-form")
	var currentTopic = "<?= $currentTopic ?>";
	const socket = new WebSocket(`${WEBSOCKET_URL}/chat/${currentTopic}`);


	//au chargement de la page
	window.addEventListener("load", () => {
		if (currentTopic) {
			let topic = currentTopic;
			history.pushState({ topic }, `chat ${topic}`, `/chat/${topic}`)

			document.querySelector(`[data-slug=${currentTopic}]`).classList.add("current")
			fetchData(currentTopic)
		}
	})

	function viewChat(ev, topic) {
		ev.preventDefault();
		if (topic != currentTopic) {
			document.querySelector(".sidebar-menu-button.current").classList.remove("current")

			ev.target.classList.add("current")
			history.pushState({ topic }, `chat ${topic}`, `/chat/${topic}`)
			fetchData(topic)
		}
	}

	// This event listener will capture when the user navigates forward or backward
	window.addEventListener('popstate', function (event) {
		if (event.state) {
			const topic = event.state.topic;

			if (topic !== currentTopic) {
				currentTopic = topic; // Update your state variable
				document.querySelector(".sidebar-menu-button.current")?.classList.remove("current");
				document.querySelector(`[data-slug=${topic}]`)?.classList.add("current");

				fetchData(topic);
			}
		} else {
			console.log('No state associated with this entry');
		}
	});


	form.addEventListener("submit", (ev) => {
		ev.preventDefault();
		const formData = new FormData(form)

		fetch(`/chat/${currentTopic}`, {
			method: 'POST',
			body: formData
		})
			.then(response => response.json())
			.then(data => {
				if (data.message) {
					socket.send(JSON.stringify(data))
					displayMessages(data)
					msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });

				}
			})
			.catch(error => {
				console.error("Error submitting the form:", error);
			}).finally(() => {
				form.reset()
			});
	})

	// When a message is received from the WebSocket server
	socket.onmessage = function (event) {
		const data = JSON.parse(event.data);
		if (data.action === "delete" && data.messages) {
			const items = document.querySelectorAll('action-menu')

			items.forEach((el) => {
				if (data.messages.includes(el.getAttribute('item-id'))) {
					el.closest('.message').remove();
				}
			})
		}

		// New chat event
		if (data.message && data.topic === currentTopic) {
			displayMessages(data, false)
			msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
		}
	}

	function fetchData(topic) {
		fetch(`/chat/api/${topic}`)
			.then((response) => {
				if (!response.ok) {
					throw new Error(`HTTP error: ${response.status}`);
				}
				return response.json();
			})
			.then((data) => {
				msgsDisplayCtn.innerHTML = "";
				if (data.messages) {
					data.messages.map(chat => {
						displayMessages(chat)
					});
				}

				msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
				currentTopic = topic;
				document.querySelector('[slot="current-label"]').textContent = currentTopic
			})
			.catch((err) => console.error(`Fetch problem: ${err.message}`));
	}

	function deleteMessage(message) {
		fetch(`/chat/${currentTopic}`, {
			method: 'DELETE',
			body: JSON.stringify({ "messages": message })
		})
			.then(response => response.text())
			.then(data => {
				socket.send(data)
			})
			.catch(error => {
				console.error("Error submitting the form:", error);
			});
	}
	function displayMessages(chat, show = true) {
		const hasOptions = show && Array.isArray(chat.options) && chat.options.length > 0;

		const msgBox = document.createElement('message-box');

		msgBox.setAttribute('pseudo', chat.pseudo)
		msgBox.setAttribute('date', chat.date)
		msgBox.setAttribute('message', chat.message)
		msgBox.setAttribute('hasOptions', hasOptions)
		msgBox.setAttribute('options', JSON.stringify(chat.options))

		msgsDisplayCtn.appendChild(msgBox)
	}

</script>
<script>
	msgsDisplayCtn.addEventListener("selected", (e) => {
		const { action, itemId } = e.detail;
		switch (action) {
			case 'delete':
				deleteMessage(itemId);
			default:
				console.log("not uspported")
		}
	});

</script>