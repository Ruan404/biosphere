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
        get => htmlspecialchars($this->title);

        set(string $title) {
            $this->title = htmlspecialchars($title);
        }
    }

    public array $actions {
        get => $this->actions;
    }

    public string $id{
        get => htmlspecialchars($this->id);

        set(string $id) {
            $this->id = htmlspecialchars($id);
        }
    }

}