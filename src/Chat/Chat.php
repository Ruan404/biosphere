<?php

namespace App\Chat;
//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Chat
{
    public int $topic_id = 0 {
        get => $this->topic_id;

        set(int $topic_id) {
            $this->topic_id = $topic_id;
        }
    }


    public string $pseudo {
        get => $this->pseudo;

        set(string $pseudo) {
            $this->pseudo = htmlspecialchars($pseudo);
        }
    }

    public string $date {
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
}