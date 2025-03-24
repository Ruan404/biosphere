<?php

namespace App\Film;
use App\Core\Database;
use PDO;
use Exception;

class FilmService
{

    private $hlsDirectory;

    public function __construct(string $hlsDirectory)
    {
        $this->hlsDirectory = $hlsDirectory;
    }

    public function generateUniqueToken() {
        return bin2hex(random_bytes(16));
    }

    public function processHLS($inputFile, $outputFolder) {
        if (!file_exists($outputFolder)) {
            mkdir($outputFolder, 0777, true);
        }

        $playlistPath = $outputFolder . "/playlist.m3u8";

        $command = escapeshellcmd("ffmpeg -i $inputFile -c:v h264 -c:a aac -b:v 1500k -f hls -hls_time 5 -hls_playlist_type vod $playlistPath");
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("Error generating HLS files.");
        }

        return $playlistPath;
    }

    public function getAllFilms(): ?array
    {
        $query = Database::getPDO()->query('SELECT cover, title FROM film JOIN genre ON film.genre_id = genre.id');

        $films = $query->fetchAll(PDO::FETCH_CLASS, Film::class);

        return $films;
    }

    public function getFilmByTitle($title): ?array
    {
        $query = Database::getPDO()->prepare('SELECT film.title, film.cover, film.video, film.description, genre.name FROM film JOIN genre ON film.genre_id = genre.id WHERE film.title = :title');
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->execute();
        $film = $query->fetch(PDO::FETCH_ASSOC);

        if ($film) {
            return $film;
        }

        return null;
    }

    public function insertVideo($title, $filePath, $playlistPath, $token) {
        $stmt = Database::getPDO()->prepare("INSERT INTO videos (title, file_path, playlist_path, token) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$title, $filePath, $playlistPath, $token]);
    }

    public function getVideoByToken($token) {
        $stmt = Database::getPDO()->prepare("SELECT * FROM videos WHERE token = ?");
        $stmt->execute([$token["token"]]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllVideos(){
        $stmt = Database::getPDO()->prepare("SELECT * FROM videos");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}