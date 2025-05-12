<?php

namespace App\Topic\Dto;

class TopicAdminPanelDto
{
    public function __construct()
    {
        $this->actions = [
            ["type" => "delete_user", "label" => "Supprimer", "confirm" => true]
        ];

        $this->id = $this->name;

    }

    public string $name {

        get => htmlspecialchars($this->name);
        set(string $name) {
            $slug = explode('/', rtrim(htmlspecialchars($name), '/'));

            $this->name = array_pop($slug);

        }
    }

    
    public string $id {
        get => $this->id;
    }

    public array $actions {
        get => $this->actions;
    }
}