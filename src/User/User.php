<?php
namespace App\User;

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
}