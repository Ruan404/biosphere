<?php
use App\Auth\AuthService;
use App\Helpers\Text;
$style = "chat";
$topics = $data['topics'] ?? [];
$currentTopic = htmlspecialchars($data['currentTopic'] ?? '');

$user = AuthService::getUserSession();

if ($user == null) {
	header('Location: /login');
	exit();
}
?>

<div class="container">
	<div class="tab-topic">
		<button class="tab-btn shadow-btn" onclick="showTab()">Topics</button>
		<?php if (!empty($currentTopic)): ?>
			<h3 class="current-topic"><?= Text::removeUnderscore($currentTopic) ?></h3>
		<?php endif ?>
	</div>
	<div class="topics">
		<button class="close-btn icon-btn" onclick="hideTab()" aria-label="close button">
			<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M20.3536 4.35355C20.5488 4.15829 20.5488 3.84171 20.3536 3.64645C20.1583 3.45118 19.8417 3.45118 19.6464 3.64645L12 11.2929L4.35355 3.64645C4.15829 3.45118 3.84171 3.45118 3.64645 3.64645C3.45118 3.84171 3.45118 4.15829 3.64645 4.35355L11.2929 12L3.64645 19.6464C3.45118 19.8417 3.45118 20.1583 3.64645 20.3536C3.84171 20.5488 4.15829 20.5488 4.35355 20.3536L12 12.7071L19.6464 20.3536C19.8417 20.5488 20.1583 20.5488 20.3536 20.3536C20.5488 20.1583 20.5488 19.8417 20.3536 19.6464L12.7071 12L20.3536 4.35355Z" />
			</svg>
		</button>
		<div class="topics-list">
			<?php foreach ($topics as $topic): ?>
				<a class='topic-link' data-slug="<?= $topic->name ?>" onclick="viewChat(event, '<?= $topic->name ?>')"
					href="#"><?= Text::removeUnderscore($topic->name) ?></a>
			<?php endforeach ?>
		</div>
	</div>
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
<script>
	var topics = document.querySelector('.topics')

	function showTab() {
		topics.classList.add('show');
		document.body.classList.add('black-mask')
	}
	function hideTab() {
		topics.classList.remove('show')
		document.body.classList.remove('black-mask')
	}
</script>
<script>
	const msgsDisplayCtn = document.querySelector(".msgs-display")
	const form = document.querySelector(".send-msg-form")
	var currentTopic = "<?= $currentTopic ?>";
	const socket = new WebSocket(`ws://localhost:3000/chat/${currentTopic}`);


	//au chargement de la page
	window.addEventListener("load", () => {
		if (currentTopic) {
			document.querySelector(`[data-slug=${currentTopic}]`).classList.add("current")
			fetchData(currentTopic)
		}
	})

	function viewChat(ev, topic) {
		ev.preventDefault();
		if (topic != currentTopic) {
			document.querySelector(".topic-link.current").classList.remove("current")

			ev.target.classList.add("current")
			history.pushState({ topic }, `chat ${topic}`, `/chat/${topic}`)

			fetchData(topic)

			currentTopic = topic
		}
	}

	// This event listener will capture when the user navigates forward or backward
	window.addEventListener('popstate', function (event) {
		if (event.state) {
			const topic = event.state.topic

			if (topic != currentTopic) {
				history.pushState({ topic }, `chat ${topic}`, `/chat/${topic}`)

				fetchData(topic)

				currentTopic = topic

				document.querySelector(".current-topic").innerText = currentTopic
				document.querySelector(".topic-link.current").classList.remove("current")
				document.querySelector(`[data-slug=${currentTopic}]`).classList.add("current")
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
					optionsEvent();
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
		if (data.messages) {
			msgsDisplayCtn.innerHTML = "";
			data.messages.map(chat => {
				displayMessages(chat);
			});
			msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
			optionsEvent();
		}

		// New chat event
		if (data.message) {
			displayMessages(data, false)
			msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
			optionsEvent();
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
				data.messages.map(chat => {
					displayMessages(chat)
				});
				msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
				currentTopic = topic;
				document.querySelector(".current-topic").innerText = currentTopic
				optionsEvent();
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
				socket.send(JSON.stringify({ "action": "new", data }))
			})
			.catch(error => {
				console.error("Error submitting the form:", error);
			});
	}

	function displayMessages(chat, show = true) {
		return (
			msgsDisplayCtn.innerHTML += `<div class='message'>
				<div class='msg-ctn'>
				<div class="msg-img">
				</div>
				<div class="msg-info-ctn">
					<div class="msg-pseudo-date-ctn">
						<p class="msg-pseudo">${chat.pseudo}</p>
						<p class="msg-date">${chat.date}</p>
					</div>
					<p>${chat.message}</p>
				</div>
			</div>
				${show ? chat.options : ""}
			</div>
			`
		)
	}
	function optionsEvent() {
		const optionTab = document.getElementsByClassName("options-btn")
		Array.from(optionTab).forEach(el => {
			el.addEventListener("click", () => {
				const options = el.parentNode.children[0];
				options.classList.toggle("show")

				window.addEventListener("click", (ev) => {
					if (ev.target !== options && ev.target !== el && options.classList.contains("show")) {
						options.classList.remove("show")
					}

				})
			})
		});
	}

</script>