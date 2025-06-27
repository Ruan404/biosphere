<?php
namespace App\User;

use App\Exceptions\BadRequestException;
use UnexpectedValueException;

class User
{

        public function __construct(string $pseudo = "", string $mdp = "", ?string $image = null)
        {
                if ($pseudo !== "") {
                        if (!preg_match('/^[\p{L}\p{N}]+$/u', $pseudo)) {
                                throw new UnexpectedValueException("Pseudo doit être alphanumérique (lettres accentuées autorisées)");
                        }

                        $this->pseudo = $pseudo;
                }

                if ($mdp !== "") {
                        $this->mdp = $mdp;
                }
        }

        public string $pseudo {
                get => $this->pseudo;
                set(string $pseudo) {
                        $this->pseudo = $pseudo;
                }
        }

        public int $id = 0 {
                get => $this->id;
        }

        public string $mdp = "" {
                get => $this->mdp;
                set(string $mdp) {
                        $this->mdp = $mdp;
                }
        }

        public string $role {
                get => $this->role;
        }

        public ?string $image = "" {
                get => $this->image;
                set(?string $image) {
                        $this->image = $image;
                }
        }
}