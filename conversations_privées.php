<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');

if (!isset($_SESSION['pseudo'])) {
	header('Location: connexion.php');
	exit;
}

if (isset($_GET['id']) and !empty($_GET['id'])) {
	$getid = $_GET['id'];

	$recupUser = $bdd->prepare('SELECT * FROM users WHERE id = ?');
	$recupUser->execute(array($getid));
	if ($recupUser->rowCount() > 0) {

		//envoyer un message
		if (isset($_POST['envoyer'])) {
			$message = nl2br(htmlspecialchars_decode($_POST['message']));
			$insertMessage = $bdd->prepare('INSERT INTO messages_privés(message, id_destinataire, id_auteur)VALUES(?, ?, ?)');
			$insertMessage->execute(array($message, $getid, $_SESSION['id']));
		}
	} else {
		echo "Aucun utilisateur trouvé";
	}


} else {
	echo "Aucun identifiant trouvé";
}
?>

<!DOCTYPE html lang="fr">
<html>

<head>
	<meta charset="UTF-8">
	<title>Messages avec Caro</title>
	<link rel="stylesheet" href="navbar.css">
	<link rel="stylesheet" href="stylesheetV21.css">
	<link rel="stylesheet" href="conv_priv.css">
	<script src="Ajax.js"></script>

</head>

<body>

	<nav>

		<div class="navbar">
			<div class="pseudos"><a href="conversations_index.php">Caro</a></div>
			<div class="logo"><a href="home.php">Biosphere</a></div>


		</div>

	</nav>


	<script>
		var lastMessageId = 0;  // conserve l'id du dernier message
		function load_messages() {
			$.ajax({
				type: 'GET',
				url: 'loadMessagesChat.php',
				data: { lastMessageId: lastMessageId, id: 12 },
				dataType: 'json',
				success: function (data) {
					if (data.messages.length > 0) {
						for (var i = 0; i < data.messages.length; i++) {
							var message = data.messages[i];
							if (message.id_auteur == '15') {
								$('#messages').append("<div class='message-right' style='text-align: right;'><h4 style='display: inline;'>" + 'Biosphère' + "</h4><span>&nbsp;</span><em style='display: inline; font-size: 0.8em;'>" + message.date + "</em><p>" + message.message + "</p></div>");
							} else {
								$('#messages').append("<div class='message-left' style='text-align: left;'><h4 style='display: inline;'>" + 'Caro' + "</h4><span>&nbsp;</span><em style='display: inline; font-size: 0.8em;'>" + message.date + "</em><p>" + message.message + "</p></div>");
							}
						}
						lastMessageId = data.lastMessageId;

						var element = document.getElementById("messages"); // scroll bas
						element.scrollTop = element.scrollHeight;
					}
					setTimeout(load_messages, 500);    // attends 500ms avant de vérifier si il y a un nouveau message
				}
			});
		}

		load_messages();   // Appel initial
	</script> <!--utilisation du jquery pour coder le côté client-->

	<section id="messages"></section>
	<form method="POST" action="" align="center">
		<div class="input-group">
			<textarea name="message" autocomplete="off" placeholder="Entrez votre message"></textarea>
			<input type="submit" name="envoyer" value="envoyer">
		</div>
	</form>








</body>

</html>