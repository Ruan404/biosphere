<?php

namespace App\Film;

//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Film
{
    public string $cover {
        get => htmlspecialchars($this->cover);

        set(string $cover) {
            $this->cover = htmlspecialchars($cover);
        }
    }

    public string $video {
        get => htmlspecialchars($this->video);

        set(string $video) {
            $this->video = htmlspecialchars($video);
        }
    }

    public int $genre_id {
        get => $this->genre_id;
    }

    public string $title {
        get => htmlspecialchars($this->title);

        set(string $title) {
            $this->title = htmlspecialchars($title);
        }
    }
    public string $description {
        get => htmlspecialchars($this->description);

        set(string $description) {
            $this->description = htmlspecialchars($description);
        }
    }

}