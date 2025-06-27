<?php

namespace App\User\Dto;
class UserAdminPanelDto
{
        // public ?string $image = null; 
        public function __set($property, $value){}

        public function __get($name){}

        public function __construct()
        {
                $this->actions = $this->setActions($this->role);
                $this->slug = $this->pseudo;
        }
        public string $pseudo {
                get => $this->pseudo;
                set(string $pseudo) {
                        $this->pseudo = $pseudo;
                }
        }


        public string $role {
                get => $this->role;
        }

        public string $slug {
                get => $this->slug;
        }

        public array $actions {
                get => $this->actions;
        }

        private function setActions($role): array
        {
                $actions = [];

                $actions[] =  ["type" => "delete_user", "label" => "Supprimer", "confirm" => true];

                if ($role !== "admin") {
                        $actions[] = ["type" => "promote_user", "label" => "Promouvoir"];
                }

                return $actions;
        }
}