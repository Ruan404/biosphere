<?php
namespace App\User;

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
}