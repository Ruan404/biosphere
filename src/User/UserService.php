<?php

namespace App\User;

use App\Core\Database;
use App\User\User;

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
}