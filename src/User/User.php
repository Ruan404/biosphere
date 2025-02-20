<?php

namespace App\User;

use App\Database\Database;
class User
{

        public string $pseudo {
                get => htmlspecialchars($this->pseudo);
        }

        public int $id {
                get => $this->id;
        }

        public string $mdp {
                get => $this->mdp;
        }

        // public string $role{

        // }

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
}