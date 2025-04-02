<?php
namespace App\Admin;


class Set_Admin {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Fonction pour supprimer un utilisateur
    public function deleteUser($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Fonction pour promouvoir un utilisateur
    public function promoteUser($id) {
        $stmt = $this->pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Fonction pour ajouter un utilisateur
    public function addUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $pseudo = $_POST['pseudo'] ?? '';
            $role = $_POST['role'] ?? 'user';
            $stmt = $this->pdo->prepare("INSERT INTO users (pseudo, role) VALUES (?, ?)");
            $stmt->execute([$pseudo, $role]);
            header('Location: /views/admin/index'); // Redirection après ajout
            exit;
        }
        include 'form_user.php'; // Inclure le formulaire d'ajout d'utilisateur
    }

    // Fonction pour supprimer un topic
    public function deleteTopic($id) {
        $stmt = $this->pdo->prepare("DELETE FROM topics WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Fonction pour ajouter un topic
    public function addTopic() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $stmt = $this->pdo->prepare("INSERT INTO topics (name) VALUES (?)");
            $stmt->execute([$name]);
            header('Location: /views/admin/index'); // Redirection après ajout
            exit;
        }
        include 'form_topic.php'; // Inclure le formulaire d'ajout de topic
    }

    // Fonction pour supprimer un podcast
    public function deletePodcast($id) {
        $stmt = $this->pdo->prepare("DELETE FROM podcasts WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Fonction pour ajouter un podcast
    public function addPodcast() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $stmt = $this->pdo->prepare("INSERT INTO podcasts (title) VALUES (?)");
            $stmt->execute([$title]);
            header('Location: /views/admin/index'); // Redirection après ajout
            exit;
        }
        include 'form_podcast.php'; // Inclure le formulaire d'ajout de podcast
    }

    // Fonction pour supprimer un film
    public function deleteFilm($id) {
        $stmt = $this->pdo->prepare("DELETE FROM films WHERE id = ?");
        $stmt->execute([$id]);
    }

    // Fonction pour ajouter un film
    public function addFilm() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $stmt = $this->pdo->prepare("INSERT INTO films (title) VALUES (?)");
            $stmt->execute([$title]);
            header('Location: /views/admin/index'); // Redirection après ajout
            exit;
        }
        include 'form_film.php'; // Inclure le formulaire d'ajout de film
    }
}

