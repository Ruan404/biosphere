<?php

namespace App\Chat;

use App\Auth\AuthService;
use App\Markdown\Spoiler\SpoilerExtension;
use ElGigi\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\CommonMarkConverter;
//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */


class Chat
{
    public function __construct()
    {
        $this->options = $this->getOptions($this->pseudo === $_SESSION['username'] || $_SESSION["role"] === "admin");

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

    public int $id = 0{
        get => $this->id;
    }
    
    public int $topic_id = 0{
        get => $this->topic_id;

        set(int $topic_id) {
            $this->topic_id = $topic_id;
        }
    }


    public string $pseudo{
        get => $this->pseudo;

        set(string $pseudo) {
            $this->pseudo = $pseudo;
        }
    }

    public string $date{
        get => $this->date;
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

    public array $options {
        get => $this->options;
    }

    private function getOptions(bool $canDelete): array
    {
        $actions = [];
        if ($canDelete) {
           $actions[] = ["label" => "Supprimer", 'value' => "delete"];
        }

        //future actions

        return $actions;
    }

    public string $htmlMessage {
        get => $this->htmlMessage;
    }
}