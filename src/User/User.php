<?php
namespace App\User;

class User
{

        public function __construct(string $pseudo = "", string $mdp = "")
        {
                if ($pseudo) {
                        $this->pseudo = $pseudo;
                }
                if ($mdp) {
                        $this->mdp = $mdp;
                }
        }
        public string $pseudo {
                get => $this->pseudo;
                set(string $pseudo) {
                        $this->pseudo = $pseudo;
                }
        }

        public int $id {
                get => $this->id;
        }

        public string $mdp {
                get => $this->mdp;
                set(string $mdp) {
                        $this->mdp = $mdp;
                }
        }

        public string $role {
                get => $this->role;
        }
}