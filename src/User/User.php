<?php
namespace App\User;

use App\Entities\Role;

class User
{

        public string $pseudo {
                get => htmlspecialchars(string: $this->pseudo);
                set(string $pseudo) {
                        $this->pseudo = htmlspecialchars(string: $pseudo);
                }
        }

        public int $id {
                get => $this->id;
        }

        public string $mdp {
                get => htmlspecialchars(string: $this->mdp);
                set(string $mdp) {
                        $this->mdp = htmlspecialchars($mdp);
                }
        }

        // public Role $role = Role::Admin {
        //         get => $this->role;
        // }

        public string $role {
                get => $this->role;
        }
}