<?php

namespace App\User;

use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\User\Dto\UserAdminPanelDto;
use App\User\User;
use Exception;
use PDO;
use PDOException;

class UserService
{
    public function createUser(User $newUser): bool
    {
        try {
            // Vérifier si l'utilisateur existe déjà
            $user = $this->getUserByPseudo($newUser->pseudo);

            $hashedPassword = password_hash($newUser->mdp, algo: PASSWORD_BCRYPT);

            if ($user === null) {
                // Ajout : définir une image par défaut en fonction de la première lettre du pseudo
                if (empty($newUser->image)) {
                    $firstLetter = strtoupper($newUser->pseudo[0]);
                    $newUser->image = $firstLetter . ".png";
                }

                $query = Database::getPDO()->prepare('INSERT INTO users(pseudo, mdp, image) VALUES(?, ?, ?)');
                $query->execute([htmlspecialchars($newUser->pseudo), $hashedPassword, $newUser->image]);
               
                return true;
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

            if ($user) {
                $user->image = $this->getAvatarUrl($user->image, $user->pseudo);
            }

            return $user ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getUserByPseudo(string $pseudo): ?User
    {
        try {
            $query = Database::getPDO()->prepare('SELECT * FROM users WHERE pseudo = ?');
            $query->execute([htmlspecialchars($pseudo)]);
            $user = $query->fetchObject(User::class);

            if ($user) {
                $user->image = $this->getAvatarUrl($user->image, $user->pseudo);
            }

            return $user ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    /**
     * NOUVELLE METHODE AJOUTEE
     */
    public function getUsers(): array
    {
        try {
            $query = Database::getPDO()->prepare('SELECT id, pseudo, image FROM users');
            $query->execute();
            $users = $query->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                $user['image'] = $this->getAvatarUrl($user['image'], $user['pseudo']);
            }

            return $users;
        } catch (\PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Fonction utilitaire pour générer l'URL de l'avatar
     */
    public function getAvatarUrl($image, $pseudo)
    {
        if (!empty($image)) {
            $filename = basename($image);
            $avatarPath = "/uploads/images/avatars/" . $filename;
            $fullPath = $_SERVER["DOCUMENT_ROOT"] . $avatarPath;
            $timestamp = file_exists($fullPath) ? filemtime($fullPath) : time();
            return $avatarPath . '?v=' . $timestamp;
        } else {
            // On s'assure que $pseudo est une chaîne non vide
            $firstLetter = 'U'; // Valeur par défaut
            if (!empty($pseudo) && is_string($pseudo)) {
                $firstLetter = strtoupper(substr($pseudo, 0, 1));
            }
            $avatarPath = "/uploads/images/avatars/{$firstLetter}.png";
            $fullPath = $_SERVER["DOCUMENT_ROOT"] . $avatarPath;
            $timestamp = file_exists($fullPath) ? filemtime($fullPath) : time();
            return $avatarPath . '?v=' . $timestamp;
        }
    }
    public function promoteToAdmin(int $userId): bool
    {
        try {
            $query = Database::getPDO()->prepare('UPDATE users SET role = ? WHERE id = ?');
            $query->execute(['admin', $userId]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }

    public function deleteUser(int $userId): bool
    {
        try {
            $query = Database::getPDO()->prepare('DELETE FROM users WHERE id = ?');
            $query->execute([$userId]);
            return true;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }

    public function deleteUsers($users)
    {
        try {
            $in = str_repeat('?,', count($users) - 1) . '?';
            $query = Database::getPDO()->prepare("DELETE FROM users WHERE pseudo IN ($in)");
            $query->execute($users);
            return $query->rowCount() > 0 ?: throw new BadRequestException("les utilisateurs n'existent pas");
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }

   public function getUsersExceptOne($userId): array
    {
        try {
            $query = Database::getPDO()->prepare('SELECT id, pseudo, role, image FROM users WHERE id!= ?');
            $query->execute([htmlspecialchars($userId)]);
            $users = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($users as &$user) {
                $user['image'] = $this->getAvatarUrl($user['image'], $user['pseudo']);
            }

            return $users;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }
}