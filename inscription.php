<?php 
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;', 'root', '');

if (isset($_POST['envoi'])){
    if(!empty($_POST['pseudo']) AND !empty($_POST['mdp'])){
	$pseudo = htmlspecialchars($_POST['pseudo']);
	$mdp = sha1($_POST['mdp']);
	$insertUser = $bdd->prepare('INSERT INTO users(pseudo, mdp)VALUES(?, ?)');
	$insertUser->execute(array($pseudo, $mdp));
	
	$recupUser = $bdd->prepare('SELECT * FROM users WHERE pseudo = ? AND mdp = ?');
	$recupUser->execute(array($pseudo, $mdp));
	if($recupUser->rowCount() > 0){
	  $_SESSION['pseudo'] = $pseudo;
	  $_SESSION['mdp'] = $mdp;
	  $_SESSION['id'] = $recupUser->fetch()['id']; 
	}	
	echo $_SESSION['id'];
	
      }else{
	echo"veuillez completez tous les champs...";
      }
}



?>
<!DOCTYPE html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Espace public</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      background-color: #f5f5f5;
    }
    form {
      background-color: #fff;
      padding: 2rem;
      border-radius: 5px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 2rem;
    }
    input[type="text"],
    input[type="password"] {
      width: 100%;
      padding: 0.5rem;
      margin-bottom: 1rem;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    input[type="submit"] {
      background-color: #4CAF50;
      color: white;
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    input[type="submit"]:hover {
      background-color: #45a049;
    }
  </style>
</head>
<body>
  <form method="POST" action="">
    <h1>Rejoins le biosphweb</h1>
    <input type="text" name="pseudo" autocomplete="off" placeholder="Entrez votre pseudo">
    <input type="password" name="mdp" autocomplete="off" placeholder="Entrez votre mot de passe">
    <input type="submit" name="envoi" value="Se connecter">
  </form>
</body>
</html>
