<?php

namespace App\Chat;

use App\Auth\AuthService;
//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Chat
{
    private $timezone;

    public function __construct(string $pseudo = "", string $date = "")
    {
        // $date = new DateTime("now", $timezone )->format('Y-m-d H:i:s');
        if($pseudo){
            $this->pseudo = $pseudo;
        }
        if($date){
            $this->date = $date;
        }
        if (session_status() === 1) {
            session_start();
        }
        $this->options = $this->getOptions($this->pseudo === $_SESSION['username'] || $_SESSION["role"] === "admin" );
    }
    public int $topic_id = 0{
        get => $this->topic_id;

        set(int $topic_id) {
            $this->topic_id = $topic_id;
        }
    }


    public string $pseudo = "" {
        get => $this->pseudo;

        set(string $pseudo) {
            $this->pseudo = htmlspecialchars($pseudo);
        }
    }

    public string $date = "" {
        get => $this->date;
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

    public string $options {
        get => $this->options;
    }

    private function getOptions(bool $canDelete): string
    {
        if ($canDelete) {
            return "
            <div class='options-ctn'>
                <div class='options'>
                    <button class='option-btn' onclick='deleteMessage(\"{$this->date}\")'>supprimer</button>
                </div>
                <button class='options-btn'>
                    <svg width='24' height='24' viewBox='0 0 24 24' fill='currentColor' xmlns='http://www.w3.org/2000/svg'>
                        <rect x='11' y='5' width='2' height='2' rx='1'/>
                        <rect x='11' y='11' width='2' height='2' rx='1'/>
                        <rect x='11' y='17' width='2' height='2' rx='1'/>
                    </svg>
                </button>
            </div>
        ";
        }

        return ""; //future reply function
    }

}