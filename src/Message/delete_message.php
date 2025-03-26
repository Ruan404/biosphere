<?php
session_start(); // Démarre la session pour récupérer les informations de l'utilisateur connecté
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', ''); // Connexion à la base de données

// Vérifier si l'utilisateur est connecté
if(!isset($_SESSION['pseudo'])){
    header('Location: login.php'); // Si non connecté, redirection vers la page de connexion
    exit;
}

// Vérifier si l'ID du message est passé dans l'URL
if(isset($_GET['id']) && !empty($_GET['id'])){
    $messageId = $_GET['id']; // Récupère l'ID du message
    
    // Récupérer le message depuis la base de données
    $recupMessage = $bdd->prepare('SELECT * FROM messages_privés WHERE id = ?');
    $recupMessage->execute(array($messageId));
    $message = $recupMessage->fetch();
    
    // Si le message existe
    if($message){
        // Vérifier si l'utilisateur est l'administrateur ou l'auteur du message
        if($_SESSION['role'] == 'admin' || $message['id_auteur'] == $_SESSION['id']){
            // Supprimer le message de la base de données
            $deleteMessage = $bdd->prepare('DELETE FROM messages_privés WHERE id = ?');
            $deleteMessage->execute(array($messageId));
            header('Location: conversations_privées.php?id=' . $message['id_destinataire']); // Rediriger vers la conversation après suppression
            exit;
        } else {
            echo "Vous n'avez pas l'autorisation de supprimer ce message."; // Si l'utilisateur n'est ni l'administrateur ni l'auteur
        }
    } else {
        echo "Message introuvable."; // Si le message n'existe pas
    }
} else {
    echo "Aucun message spécifié pour la suppression."; // Si l'ID n'est pas spécifié
}
?>
