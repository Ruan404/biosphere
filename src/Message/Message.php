<?php
namespace App\Message;
session_start();
use App\Message;
use App\Message\delete_user;
use App\Message\delete_message;
use PDO;

// Connexion à la base de données
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['pseudo'])) {
    header('Location: /login');
    exit;
}

// Vérifier le rôle de l'utilisateur
$isAdmin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');

// Récupérer la liste des utilisateurs (sauf l'utilisateur connecté)
$recupUsers = $bdd->prepare('SELECT id, pseudo FROM users WHERE id != ?');
$recupUsers->execute([$_SESSION['id']]);
$users = $recupUsers->fetchAll(PDO::FETCH_ASSOC);

// Récupérer l'ID de l'utilisateur sélectionné (s'il existe)
$selectedUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

// Récupérer les messages échangés avec l'utilisateur sélectionné
$messages = [];
if ($selectedUserId) {
    $recupMessages = $bdd->prepare('SELECT * FROM messages_privés WHERE (id_auteur = ? AND id_destinataire = ?) OR (id_auteur = ? AND id_destinataire = ?) ORDER BY id ASC');
    $recupMessages->execute([$_SESSION['id'], $selectedUserId, $selectedUserId, $_SESSION['id']]);
    $messages = $recupMessages->fetchAll(PDO::FETCH_ASSOC);
}

// Envoyer un message
if (isset($_POST['envoyer']) && $selectedUserId) {
    $message = nl2br(htmlspecialchars($_POST['message']));
    if (!empty($message)) {
        $insertMessage = $bdd->prepare('INSERT INTO messages_privés(message, id_destinataire, id_auteur) VALUES(?, ?, ?)');
        $insertMessage->execute([$message, $selectedUserId, $_SESSION['id']]);
        // Rediriger pour éviter la soumission multiple du formulaire
        header('Location: /Message.php?user_id=' . $selectedUserId);
        exit;
    }
}

// Supprimer un message (pour les administrateurs)
if ($isAdmin && isset($_GET['delete_message'])) {
    $messageId = intval($_GET['delete_message']);
    $deleteMessage = $bdd->prepare('DELETE FROM messages_privés WHERE id = ?');
    $deleteMessage->execute([$messageId]);
    // Rediriger pour éviter la suppression multiple
    header('Location: /Message.php?user_id=' . $selectedUserId);
    exit;
}

// Supprimer un utilisateur (pour les administrateurs)
if ($isAdmin && isset($_GET['delete_user'])) {
    $userId = intval($_GET['delete_user']);
    // Supprimer les messages de l'utilisateur
    $deleteUserMessages = $bdd->prepare('DELETE FROM messages_privés WHERE id_auteur = ? OR id_destinataire = ?');
    $deleteUserMessages->execute([$userId, $userId]);
    // Supprimer l'utilisateur
    $deleteUser = $bdd->prepare('DELETE FROM users WHERE id = ?');
    $deleteUser->execute([$userId]);
    // Rediriger pour éviter la suppression multiple
    header('Location: mMssage.php');
    exit;
}
?>