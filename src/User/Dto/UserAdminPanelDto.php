<?php

namespace App\User\Dto;
class UserAdminPanelDto{
    public function __construct()
    {
            $this->actions = $this->setActions($this->role);
    }
    public string $pseudo {
            get => htmlspecialchars(string: $this->pseudo);
            set(string $pseudo) {
                    $this->pseudo = htmlspecialchars(string: $pseudo);
            }
    }


    public string $role {
            get => $this->role;
    }

    public array $actions {
            get => $this->actions;
    }

    private function setActions($role): array{
        $actions = [];
        
        array_push($actions, ["type" => "delete_user", "label" => "Supprimer", "confirm" => true]);

        if($role !== "admin"){
            array_push($actions, ["type" => "promote_user", "label" => "Promouvoir"]);
        }

        return $actions;
    }
}