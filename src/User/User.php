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

        //set in string and get in Role
        public String|Role $role {
                get => Role::tryFrom($this->role);
        }
}