<?php
namespace App\User;

class User
{

        public function __construct(string $pseudo = "", string $mdp = "")
        {
                if ($pseudo) {
                        $this->pseudo = htmlspecialchars($pseudo);
                }
                if ($mdp) {
                        $this->mdp = htmlspecialchars($mdp);
                }
        }
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

        public string $role {
                get => $this->role;
        }
}