<?php

namespace App\Film\Dto;

class FilmAdminPanelDto
{

    public function __construct()
    {
        $this->actions = [
            ["type" => "delete_film", "label" => "Supprimer", "confirm" => true]
        ];
    }


    public string $title {
        get => $this->title;

        set(string $title) {
            $this->title = $title;
        }
    }

    public array $actions {
        get => $this->actions;
    }

    public string $slug{
        get => $this->slug;

        set(string $slug) {
            $this->slug = $slug;
        }
    }

}