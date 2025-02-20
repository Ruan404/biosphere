<?php
use App\Chat\{
	Chat
};

use App\Topic\{
	Topic
};

$style = "chat";

$pdo = new PDO('mysql:host=localhost:3306;dbname=espace_membres;charset=utf8;', 'root', '', [
	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);
$query = $pdo->query('SELECT * FROM topic ORDER BY topic.name ASC');
$topics = $query->fetchAll(PDO::FETCH_CLASS, Topic::class);
?>

<?php
/**
 * récupération des messages du chat en fonction du topic passé en url
 * 
 */


if (isset($params['slug'])) {
	$topic = new Topic()->getTopicByName($params['slug']);
	if ($topic == null) {
		header('Location: ' . $router->url('chat'));
		exit();
	}
	$topicId = $topic->id;


	// récupère l'id du dernier message affiché
	$lastMessageId = $_GET['lastMessageId'] ?? 0;
	$messages = new Chat()->getChatMessages($topicId, $lastMessageId);

	if ($messages == null) {
		header('Location: ' . $router->url('chat'));
		exit();
	}
}

?>

<?php
if (!empty($_POST)) {
	//topic does not exists
	if ($topic == null) {
		header('Location: ' . $router->url('chat'));
		exit();
	}

	//create a new chat
	$chat = new Chat();
	$chat->message = $_POST['message'];
	$chat->pseudo = "Biosphère";
	$chat->topic_id = $topicId;

	$result = $chat->addMessage($chat);

	// redirection vers la page chat.php avec le topic sélectionné
	header('Location: ' . $router->url('topic', ['slug' => $topic->name]));

	exit();
}

?>

<div class="container">
	<button class="tab-btn tertiary-btn" onclick="showTab()"><?= $params['slug'] ?? 'voir les topics' ?></button>
	<div class="topics">
		<button class="close-btn icon-btn" onclick="hideTab()" aria-label="close button">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
				<path
					d="M20.3536 4.35355C20.5488 4.15829 20.5488 3.84171 20.3536 3.64645C20.1583 3.45118 19.8417 3.45118 19.6464 3.64645L12 11.2929L4.35355 3.64645C4.15829 3.45118 3.84171 3.45118 3.64645 3.64645C3.45118 3.84171 3.45118 4.15829 3.64645 4.35355L11.2929 12L3.64645 19.6464C3.45118 19.8417 3.45118 20.1583 3.64645 20.3536C3.84171 20.5488 4.15829 20.5488 4.35355 20.3536L12 12.7071L19.6464 20.3536C19.8417 20.5488 20.1583 20.5488 20.3536 20.3536C20.5488 20.1583 20.5488 19.8417 20.3536 19.6464L12.7071 12L20.3536 4.35355Z" />
			</svg>
		</button>
		<div class="topics-list">
			<?php foreach ($topics as $topic): ?>
				<?php if (isset($params['slug']) && $topic->name == $params['slug']): ?>
					<a class='topic-link current'
						href="<?= $router->url('topic', ['slug' => $topic->name]) ?>"><?= $topic->name ?></a>
				<?php else: ?>
					<a class='topic-link' href="<?= $router->url('topic', ['slug' => $topic->name]) ?>"><?= $topic->name ?></a>
				<?php endif ?>
			<?php endforeach ?>
		</div>
	</div>
	<div class="messages">
		<?php if (!empty($messages)): ?>
			<div class="msgs-display">
				<?php foreach ($messages as $message): ?>
					<div class='msg-ctn'>
						<div class="msg-img">

						</div>
						<div class="msg-info-ctn">
							<div class="msg-pseudo-date-ctn">
								<p class="msg-pseudo"><?= $message->pseudo ?></p>
								<p class="msg-date"><?= $message->date ?></p>
							</div>
							<p><?= $message->message ?></p>
						</div>
					</div>
				<?php endforeach ?>
			</div>

			<form class="send-msg-form" method="POST" align="center">
				<textarea name="message" required autocomplete="off" placeholder="Entrez votre message"></textarea>
				<input class="primary-btn" type="submit" name="valider" value="envoyer">
			</form>
		<?php else: ?>
			<div class="no">Choisis un topic...</div>
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