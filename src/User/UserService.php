<?php

namespace App\User;

use App\Auth\AuthService;
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
                $query = Database::getPDO()->prepare('INSERT INTO users(pseudo, mdp)VALUES(?, ?)');
                $query->execute([$newUser->pseudo, $hashedPassword]);

                return $query->rowCount() > 0;
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
            $query = Database::getPDO()->prepare('SELECT * FROM users WHERE pseudo = ?');
            $query->execute([$pseudo]);
            $user = $query->fetchObject(User::class);

            return $user ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function promoteToAdmin(int $userId): bool
    {
        try {
            $query = Database::getPDO()->prepare('UPDATE users SET role = ? WHERE id = ?');
            $query->execute(['admin', $userId]);

            return $query->rowCount() > 0;

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

            return $query->rowCount() > 0;

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

    public function adminUsersExceptOne($userId): array
    {
        try {
            $query = Database::getPDO()->prepare('SELECT pseudo, role FROM users WHERE id!= ?');
            $query->execute([$userId]);
            $users = $query->fetchAll(PDO::FETCH_CLASS, UserAdminPanelDto::class);

            return $users;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }

    public function getUsersExceptOne($userId): array
    {
        try {
            $query = Database::getPDO()->prepare('SELECT image, pseudo, role FROM users WHERE id!= ?');
            $query->execute([htmlspecialchars($userId)]);
            $users = $query->fetchAll(PDO::FETCH_CLASS, User::class);

            return $users;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong.");
        }
    }
    public function getUserActions($name, $role, $owner, $resource): array
    {
        $actions = [];
        $authService = new AuthService();
        $sub = (object) ["Name" => $name, "Owner" => $owner, "Role" => $role];

        $canDelete = $authService->canPerform($sub, $resource, "DELETE");

        if ($canDelete) {
            $actions[] = ["label" => "Supprimer", "value" => "delete"];
        }

        return $actions;
    }

    public function updateUserAvatar($username, $avatarFilename)
    {
        try {
            $pdo = Database::getPDO();
            $stmt = $pdo->prepare("UPDATE users SET image = ? WHERE pseudo = ?");
            $stmt->execute([$avatarFilename, $username]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'avatar : " . $e->getMessage());
        }
    }

}