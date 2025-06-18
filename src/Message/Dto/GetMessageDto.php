<?php

namespace App\Message\Dto;

class GetMessageDto
{
    public ?string $image = null; 
    
    public function __construct()
    {
        $this->isAuthor = $_SESSION['user_id'] === $this->id_auteur;
        $this->canDelete = $this->pseudo === $_SESSION['username'] || $_SESSION["role"] === "admin";
        $this->options = $this->getOptions($this->pseudo === $_SESSION['username'] || $_SESSION["role"] === "admin");
    }

    public int $id {
        get => $this->id;
    }

    public int $id_destinataire {
        get => $this->id_destinataire;

        set(int $id_destinataire) {
            $this->id_destinataire = $id_destinataire;
        }
    }

    public int $id_auteur {
        get => $this->id_auteur;

        set(int $id_auteur) {
            $this->id_auteur = $id_auteur;
        }
    }

    public string $pseudo {
        get => $this->pseudo;
    }

    public string $message {
        /**
         * 1. change special to html tag
         * 2. remove html tags
         * 3. remove spaces
         * 4. nl2br permet à l'utilisateur de sauter des lignes
         */
        get => nl2br(rtrim(strip_tags(htmlspecialchars_decode(trim($this->message)))));

        set(string $message) {
            $this->message = nl2br(rtrim(strip_tags(htmlspecialchars_decode(trim($message)))));
        }
    }

    public bool $isAuthor {
        get => $this->isAuthor;
    }

    public array $options {
        get => $this->options;
    }

    public string $date {
        get => htmlspecialchars($this->date);

        set(string $date) {
            $this->date = htmlspecialchars($date);
        }
    }

    public bool $canDelete{
        get => $this->canDelete;
    }
    
    private function getOptions(bool $canDelete): array
    {
        $actions = [];
        if ($canDelete) {
            array_push($actions, ["label" => "supprimer", 'value' => "delete"]);
        }

        //future actions

        return $actions;
    }

}