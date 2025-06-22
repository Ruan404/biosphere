<?php

namespace App\Message;

use App\Markdown\Spoiler\SpoilerExtension;
use ElGigi\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\CommonMarkConverter;

class Message
{
    public function __construct()
    {
        $converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'renderer' => [
                'soft_break' => "<br />\n",
            ]
        ]);
        $converter->getEnvironment()->addExtension(new EmojiExtension);
        $converter->getEnvironment()->addExtension(new SpoilerExtension);


        $this->htmlMessage = $converter($this->message)->getContent();
    }

    public string $recipient = "" {
        get => $this->recipient;
    }

    public string $sender = "" {
        get => $this->sender;
    }

    public int $id = 0 {
        get => $this->id;
    }

    public int $id_destinataire = 0 {
        get => $this->id_destinataire;

        set(int $id_destinataire) {
            $this->id_destinataire = $id_destinataire;
        }
    }

    public int $id_auteur = 0 {
        get => $this->id_auteur;

        set(int $id_auteur) {
            $this->id_auteur = $id_auteur;
        }
    }

    public string $message {
        /**
         * 1. change special to html tag
         * 2. remove html tags
         * 3. remove spaces
         * 4. nl2br permet à l'utilisateur de sauter des lignes
         */
        get => $this->message;

        set(string $message) {
            $this->message = $message;
        }
    }

    public string $htmlMessage {
        get => $this->htmlMessage;
    }

    public string $date {
        get => $this->date;

        set(string $date) {
            $this->date = $date;
        }
    }
}