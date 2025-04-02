<?php

namespace App\Film;

//voir les messages par rapport à un topic
/**
 * récupérer le topic
 * renvoyer les messages correspondant au topic
 */
class Film
{
    public string $cover_image {
        get => htmlspecialchars($this->cover_image);

        set(string $cover_image) {
            $this->cover_image = htmlspecialchars($cover_image);
        }
    }

    public string $video {
        get => htmlspecialchars($this->video);

        set(string $video) {
            $this->video = htmlspecialchars($video);
        }
    }
    public int $id = 0{
        get => $this->id;
    }
    public int $id = 0{
        get => $this->id;
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

    public string $token{
        get => htmlspecialchars($this->token);

        set(string $token) {
            $this->token = htmlspecialchars($token);
        }
    }
    public string $description {
        get => htmlspecialchars($this->description);

        set(string $description) {
            $this->description = htmlspecialchars($description);
        }
    }

    public string $file_path {
        get => $this->file_path;
    }
    public string $playlist_path {
        get => $this->playlist_path;
    }
}