<?php

namespace App\Chat;
//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Chat
{
    public int $topic_id {
        get => $this->topic_id;

        set(int $topic_id) {
            $this->topic_id = $topic_id;
        }
    }


    public string $pseudo {
        get => htmlspecialchars($this->pseudo);

        set(string $pseudo) {
            $this->pseudo = $pseudo;
        }
    }

    public string $date {
        get => $this->date;
    }

    public string $message {

        //nl2br permet à l'utilisateur de sauter des lignes
        get => nl2br(rtrim(strip_tags(htmlspecialchars($this->message))));

        set(string $message) {
            $this->message = trim($message);
        }
    }
}