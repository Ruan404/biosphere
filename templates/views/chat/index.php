<?php
use App\Helpers\Text;
use App\User\UserService;
$style = "chat";
$topics = $data['topics'] ?? [];
$currentTopic = htmlspecialchars($data['currentTopic'] ?? '');

if (session_status() == 1) {
	session_start();
}

$user = new UserService()->getUserById($_SESSION['auth']);

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
				<?php if (!empty($currentTopic) && $topic->name == $currentTopic): ?>
					<a class='topic-link current'
						href="<?= '/chat/' . $topic->name ?>"><?= Text::removeUnderscore($topic->name) ?></a>
				<?php else: ?>
					<a class='topic-link' href="<?= '/chat/' . $topic->name ?>"><?= Text::removeUnderscore($topic->name) ?></a>
				<?php endif ?>
			<?php endforeach ?>
		</div>
	</div>
	<div class="messages">
		<?php if (!empty($currentTopic)): ?>
			<div class="msgs-display">
			</div>
			<form class="send-msg-form">
				<textarea name="message" required autocomplete="off" placeholder="Entrez votre message"></textarea>
				<input class="primary-btn" type="submit" name="valider" value="envoyer">
			</form>
		<?php else: ?>
			<div class="no-topic">aucun topic sélectionné</div>
		<?php endif ?>
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

	const topic = "<?= $currentTopic ?>";

	if (topic) {
		// Create a WebSocket connection to the server
		const socket = new WebSocket(`ws://localhost:8000/chat/${topic}`);

		// When WebSocket connection is open
		socket.onopen = function () {
			console.log("Connected to WebSocket server");
		};

		// When a message is received from the WebSocket server
		socket.onmessage = function (event) {
			const data = JSON.parse(event.data);
			// Display message if it's a chat message
			if (data.message) {
				const msgCtn = document.createElement("div")
				msgCtn.classList.add("msg-ctn")
				msgCtn.innerHTML = `
			
				<div class="msg-img">
				</div>
				<div class="msg-info-ctn">
					<div class="msg-pseudo-date-ctn">
						<p class="msg-pseudo">${data.pseudo}</p>
						<p class="msg-date">${data.date}</p>
					</div>
					<p>${data.message}</p>
				</div>
			
		`
				msgsDisplayCtn.appendChild(msgCtn)

				msgsDisplayCtn.scroll({ top: msgsDisplayCtn.scrollHeight, behavior: 'smooth' });

			}
		};

		const form = document.querySelector(".send-msg-form")

		form.addEventListener("submit", (ev) => {
			ev.preventDefault();
			const formData = new FormData(form)

			const formObject = {
				pseudo: "<?= $user->pseudo ?>",
				topic: `<?= $currentTopic ?>`,
				message: formData.get("message")
			}

			socket.send(JSON.stringify(formObject));
		})
	}
</script>