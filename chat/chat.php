<?php 
session_start();

if(!$_SESSION['mdp']){
	header('connexion.php');
}
echo $_SESSION['pseudo'];
?>

<!DOCTYPE html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messagerie Commune</title>
</head>
<body>
  <form methods="POST" actions="" align="center">
	<input types="text">
	<textarea name="message"></textarea>
	<br>
	<input type="submit" name="valider">
  </form>
  <section id="messages"></section>
</body>
</html>

