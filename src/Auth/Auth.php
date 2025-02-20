<?php

namespace App\Auth;

use PDO;
use \App\User\User;

class Auth
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(string $pseudo, string $password): ?User
    {
        //get user
        $query = $this->pdo->prepare('SELECT * FROM users WHERE pseudo = ?');
        $query->execute([htmlspecialchars($pseudo)]);
        $user = $query->fetchObject(User::class);
      
        if ($user == false) {
            return null;
        }
        //verify password
        if (sha1($password) == $user->mdp) {
            /**
             * 0 ----> PHP_SESSION_DISABLED if sessions are disabled.
             * 1 ----> PHP_SESSION_NONE if sessions are enabled, but none exists.
             * 2 ----> PHP_SESSION_ACTIVE if sessions are enabled, and one exists.
             */
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
                $_SESSION['auth'] = $user->id;
                return $user;
            }

            return $user;
        }

        return null;
    }

    public function signup(string $pseudo, string $password): bool
    {
        //verify if the user already exists in the database
        $query = $this->pdo->prepare('SELECT * FROM users WHERE pseudo = ?');
        $query->execute([htmlspecialchars($pseudo)]);
        $user = $query->fetchObject(User::class);

        // dd($user);

        if ($user === false) {
            $req = $this->pdo->prepare('INSERT INTO users(pseudo, mdp)VALUES(?, ?)');
            $result = $req->execute([htmlspecialchars($pseudo), sha1($password)]);
            
        }

        return false;
    }

    /**
     * la fonction est statique pour ne pas avoir à instancier la classe
     * instancier signifie créer un nouvel objet à partir d'une classe
     * exemple : $auth = new Auth($pdo);
     * $pdo sera à définir
     */
    public static function logout()
    {
        // Initialize the session.
        // If you are using session_name("something"), don't forget it now!
        session_start();

        // Unset all of the session variables.
        $_SESSION = array();

        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        // Finally, destroy the session.
        session_destroy();
    }
}