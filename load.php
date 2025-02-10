<?php
	session_start();
	echo "cozerzerucou"
?>
	
<!DOCTYPE html lang="fr">
<html>
<head>
  <meta charset="UTF-8">
  <title>Messagerie Commune</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
  
<style>
	#messages {
		height: 300px;
		overflow-y: auto;
	}

		.message {
		margin-bottom: 10px;
	  }
</style>

</head>
<body>
<?php
	try {
		$bdd = new PDO('mysql:host=localhost;dbname=messagerie;charset=utf8;', 'phpmyadmin', '',array(PDO::ATTR_ERRMODE =>PDO::ERRMODE_EXCEPTION));
		$recupMessages = $bdd->query('SELECT * FROM messages ORDER BY id DESC');  //query permet d'afficher tous les masseages contenus dans la section "messages"
		while($message = $recupMessages->fetch())
		{
			echo "<div class='message'>";
			echo "<h4>";
			echo htmlspecialchars($message['pseudo']);
			echo "</h4>";
			echo "<br>";
			echo "<p>";
			echo nl2br(htmlspecialchars($message['message']));
			echo "</p>";
			echo "</div>";
		}
	}catch( Exception $e) {
	echo $e->getMessage(), '\n';
	}
?>
	<script>
		setInterval('load_messages()',500);    //intervalle d'exécution de la boucle
		function load_messages(){
			$('#messages').load('loadMessages.php')
		}
	</script>                     <!--utilisation du jquery pour coder le côté client-->

</body>
</html>
