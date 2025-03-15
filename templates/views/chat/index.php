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
	const messageCtn = document.querySelector(".messages")
	const msgsDisplayCtn = document.querySelector(".msgs-display")
	const form = document.querySelector(".send-msg-form")
	var currentTopic = "<?= $currentTopic ?>";

	document.querySelector(`[data-slug=${currentTopic}]`).classList.add("current")

	const socket = new WebSocket(`ws://localhost:8000/chat/${currentTopic}`);


	function viewChat(ev, chatTopic) {
		ev.preventDefault();
		if (chatTopic != currentTopic) {
			document.querySelector(".topic-link.current").classList.remove("current")

			ev.target.classList.add("current")
			history.pushState({ chatTopic }, `chat ${chatTopic}`, `/chat/${chatTopic}`)

			fetchdata(chatTopic)

			currentTopic = chatTopic
		}
	}

	// This event listener will capture when the user navigates forward or backward
	window.addEventListener('popstate', function (event) {
		if (event.state) {
			const chatTopic = event.state.chatTopic

			if (chatTopic != currentTopic) {
				history.pushState({ chatTopic }, `chat ${chatTopic}`, `/chat/${chatTopic}`)

				fetchdata(chatTopic)

				currentTopic = chatTopic
				console.log(currentTopic)
				
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
			method: 'POST',  // Send a POST request
			body: formData   // Attach the form data
		})
			.then(response => response.json())  // Assuming the response is in JSON format
			.then(data => {
				if (data) {
					console.log(data);
					socket.send(JSON.stringify(data))
					//add the new message
				}
			})
			.catch(error => {
				console.error("Error submitting the form:", error);
				// Handle any error that occurs during the fetch
			});
	})

	// When a message is received from the WebSocket server
	socket.onmessage = function (event) {
		const data = JSON.parse(event.data);
		// Display message if it's a chat message
		if (data.message) {
			msgsDisplayCtn.innerHTML += `
				<div class='msg-ctn'>
					<div class="msg-img">
					</div>
					<div class="msg-info-ctn">
						<div class="msg-pseudo-date-ctn">
							<p class="msg-pseudo">${data.pseudo}</p>
							<p class="msg-date">${data.date}</p>
						</div>
						<p>${data.message}</p>
					</div>
				</div>
			
		`
			msgsDisplayCtn.appendChild(msgCtn)
			msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });
		}
	}

	function fetchdata(topic) {
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
					msgsDisplayCtn.innerHTML += `
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
					`;
				});

				currentTopic = topic;
			})
			.catch((err) => console.error(`Fetch problem: ${err.message}`));
	}

</script>