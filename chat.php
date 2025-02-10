<?php
session_start();

if (!isset($_SESSION['pseudo'])) {
	header('Location: connexion.php');
	exit;
}

$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');

$topic = isset($_GET['topic']) ? $_GET['topic'] : 'general';     //définition du topic en fonction de l'URL de la page

if (isset($_POST['valider'])) {
	$message = trim($_POST['message']);  /* trim = on supprime tous les espaces inutiles du message */
	if (!empty($message) && $message != '') {
		$message = nl2br(rtrim(strip_tags(htmlspecialchars_decode($_POST['message'])))); //nl2br permet à l'utilisateur de sauter des lignes

		$insertMessage = $bdd->prepare('INSERT INTO chat(pseudo, message, topic) VALUES(?,?,?)');
		$insertMessage->execute(array($_SESSION['pseudo'], $message, $topic));
		header('Location: chatV4.php?topic=' . $topic);   // redirection vers la page chat.php avec le topic sélectionné
		exit;
	} else {
		echo '<span style="color:red;">Veuillez saisir un message...</span>';
	}
}

?>

<!DOCTYPE html lang="fr">
<html>

<head>
	<meta charset="UTF-8">
	<title>Messagerie Commune</title>
	<script src="Ajax.js"></script>
	<link rel="stylesheet" href="style.css"> <!-- Ajout du CSS général -->
	<link rel="stylesheet" href="navbar.css">
	<link rel="stylesheet" href="chat.css">

</head>

<body>
	<nav>
		<div class="navbar">
			<div class="logo"><a href="home.php">Biosphere</a></div>
		</div>
	</nav>

	<script>
        var lastMessageId = 0; // conserve l'id du dernier message
        function load_messages(){
            $.ajax({
                type: 'GET',
                url: 'loadMessagesV3.php',
                data: {lastMessageId: lastMessageId, topic: "commentaires_de_film"},
                dataType: 'json',
                success: function(data) {
                    if (data.messages.length > 0) {
                        for (var i = 0; i < data.messages.length; i++) {
                            var message = data.messages[i];
                            if (message.pseudo == 'Biosphère') {
                                $('#messages').append("<div class='message-right'><h4 style='display: inline;'>" + message.pseudo + "</h4><span>&nbsp;</span><em style='display: inline; font-size: 0.8em;'>" + message.date + "</em><p>" + message.message + "</p></div>");
                            } else {
                                $('#messages').append("<div class='message-left'><h4 style='display: inline;'>" + message.pseudo + "</h4><span>&nbsp;</span><em style='display: inline; font-size: 0.8em;'>" + message.date + "</em><p>" + message.message + "</p></div>");
                            }
                        }
                        lastMessageId = data.lastMessageId;
                        var element = document.getElementById("messages"); // Scroll bas
                        element.scrollTop = element.scrollHeight;
                    }
                    setTimeout(load_messages, 500); // attends 500ms avant de vérifier si il y a un nouveau message
                }
            });
        }
        
        load_messages(); // Appel initial
    </script>

	<section id="messages"></section>
	<form method="POST" action="" align="center">
		<div class="input-group">
			<textarea name="message" autocomplete="off" placeholder="Entrez votre message"></textarea>
			<input type="submit" name="valider" value="envoyer">
		</div>
	</form>
</body>

</html>