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
        get => $this->cover_image;

        set(string $cover_image) {
            $this->cover_image = $cover_image;
        }
    }

    public string $video {
        get => $this->video;

        set(string $video) {
            $this->video = $video;
        }
    }
    public int $id = 0{
        get => $this->id;
    }

    public int $genre_id {
        get => $this->genre_id;
    }

    public string $title {
        get => $this->title;

        set(string $title) {
            $this->title = $title;
        }
    }

    public string $token{
        get => $this->token;

        set(string $token) {
            $this->token = $token;
        }
    }
    public string $description {
        get => $this->description;

        set(string $description) {
            $this->description = $description;
        }
    }

    public string $file_path {
        get => $this->file_path;
    }
    public string $playlist_path {
        get => $this->playlist_path;
    }
}