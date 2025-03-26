<?php

namespace App\User;

use App\Core\Database;
use App\User\User;
use PDO;

class UserService
{
    
    public function createUser(User $user): bool
    {

        //verify if the user already exists in the database
        $query = Database::getPDO()->prepare('SELECT * FROM users WHERE pseudo = ?');
        $query->execute([htmlspecialchars($user->pseudo)]);
        $getUser = $query->fetchObject(User::class);

        if ($getUser === false) {
            $req = Database::getPDO()->prepare('INSERT INTO users(pseudo, mdp)VALUES(?, ?)');
            $result = $req->execute([htmlspecialchars($user->pseudo), sha1($user->mdp)]);

            return $result;
        }

        return false;

    }

    public function getUserById($id): ?User
    {
        /**
         * verifier qu'une session existe
         * renvoyer l'utilisateur à l'aide de l'ID
         */
        $query = Database::getPDO()->prepare('SELECT * FROM users WHERE id = ?');
        $query->execute([$id]);
        
        $user = $query->fetchObject(User::class);
        
        if ($user == false) {
            return null;
        }

        
        return $user;
    }

    public function getUserByPseudo($pseudo): ?User{
        //get user
        $query = Database::getPDO()->prepare('SELECT * FROM users WHERE pseudo = ?');
        $query->execute([htmlspecialchars($pseudo)]);
        $user = $query->fetchObject(User::class);
        if ($user == false) {
            return null;
        }

        return $user;
    }

    // Fonction pour vérifier les identifiants de l'utilisateur (connexion)
    public function checkCredentials($pseudo, $mdp): ?User
     {
         $query = Database::getPDO()->prepare('SELECT * FROM users WHERE pseudo = ? AND mdp = ?');
         $query->execute([htmlspecialchars($pseudo), sha1($mdp)]);
         
         $user = $query->fetchObject(User::class);
 
         if ($user == false) {
             return null;
         }
 
         return $user;
     }
 
     // Fonction pour promouvoir un utilisateur en administrateur
     public function promoteToAdmin(int $userId): bool
     {
         $query = Database::getPDO()->prepare('UPDATE users SET role = ? WHERE id = ?');
         $result = $query->execute(['admin', $userId]);
 
         return $result;
     }
 
     // Fonction pour supprimer un utilisateur
     public function deleteUser(int $userId): bool
     {
         $query = Database::getPDO()->prepare('DELETE FROM users WHERE id = ?');
         $result = $query->execute([$userId]);
 
         return $result;
     }
     
     public function getUsers(){
        if(session_status() == 1){
            session_start();
        }
        $query = Database::getPDO()->prepare('SELECT * FROM users WHERE id!= ?');
        $query->execute([htmlspecialchars($_SESSION['user_id'])]);
        return $query->fetchAll(PDO::FETCH_CLASS, User::class);
     }
}