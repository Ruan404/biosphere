<?php
session_start();
$bdd = new PDO('mysql:host=localhost;dbname=espace_membres;charset=utf8;', 'root', '');

// Vérifier si l'utilisateur est un administrateur
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: conversations_index.php');
    exit();
}

// Vérifier si l'ID de l'utilisateur à supprimer est passé
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Supprimer l'utilisateur de la base de données
    $deleteUser = $bdd->prepare('DELETE FROM users WHERE id = :id');
    $deleteUser->execute(['id' => $userId]);

    // Redirection vers la gestion des utilisateurs après suppression
    header('Location: conversations_index.php');
    exit();
} else {
    // Redirection si l'ID n'est pas fourni
    header('Location: conversations_index.php');
    exit();
}
