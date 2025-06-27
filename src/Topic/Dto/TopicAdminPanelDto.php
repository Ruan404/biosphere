<?php

namespace App\Topic\Dto;

class TopicAdminPanelDto
{
    public function __construct()
    {
        $this->actions = [
            ["type" => "delete_topic", "label" => "Supprimer", "confirm" => true]
        ];

        $this->slug = $this->name;

    }

    public string $name {

        get => $this->name;
        set(string $name) {
            $slug = explode('/', rtrim($name, '/'));

            $this->name = array_pop($slug);

        }
    }

    
    public string $slug {
        get => $this->slug;
    }

    public array $actions {
        get => $this->actions;
    }
}