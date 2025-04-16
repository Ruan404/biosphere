<?php

namespace App\User;

use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use App\User\User;
use Exception;
use PDO;
use PDOException;

class UserService
{
    public function createUser(User $newUser): string
    {
        try {
            //verify if the user already exists in the database
            $user = $this->getUserByPseudo($newUser->pseudo);

            if ($user === null) {
                $query = Database::getPDO()->prepare('INSERT INTO users(pseudo, mdp)VALUES(?, ?)');
                $query->execute([htmlspecialchars($newUser->pseudo), sha1($newUser->mdp)]);
                $response = "your account has been created";
                return $response;
            }
            throw new BadRequestException("user already exist");

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getUserById(int $id): ?User
    {
        try {
            $query = Database::getPDO()->prepare('SELECT * FROM users WHERE id = ?');
            $query->execute([$id]);

            $user = $query->fetchObject(User::class);

            return $user ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getUserByPseudo(string $pseudo): ?User
    {
        try {
            //get user
            $query = Database::getPDO()->prepare('SELECT * FROM users WHERE pseudo = ?');
            $query->execute([htmlspecialchars($pseudo)]);
            $user = $query->fetchObject(User::class);

            return $user ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    /**
     * Fonction pour promouvoir un utilisateur en administrateur
     * @param int $userId
     * @return bool
     */
    public function promoteToAdmin(int $userId): string
    {
        try {
            $user = $this->getUserById($userId);

            if($user===null){
                throw new NotFoundException("user was not found");
            }

            $query = Database::getPDO()->prepare('UPDATE users SET role = ? WHERE id = ?');
            $query->execute(['admin', $user->id]);

            return "user $user->pseudo has been successfully promoted";

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }

    }


    /**
     * Fonction pour supprimer un utilisateur
     * @param int $userId
     * @return bool
     */
    public function deleteUser(int $userId): string
    {
        try {
            $user = $this->getUserById($userId);

            $query = Database::getPDO()->prepare('DELETE FROM users WHERE id = ?');
            $query->execute([$user->id]);
            $response = "user $user->pseudo has been successfully deleted";

            return $response;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }

    public function getUsers(): array
    {
        try {
            if (session_status() == 1) {
                session_start();
            }
            $query = Database::getPDO()->prepare('SELECT * FROM users WHERE id!= ?');
            $query->execute([htmlspecialchars($_SESSION['user_id'])]);
            $users = $query->fetchAll(PDO::FETCH_CLASS, User::class);

            return $users;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }
}