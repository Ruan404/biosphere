<?php

namespace App\Film;
use App\Core\Database;
use PDO;

class FilmService
{

    private $uploadDirectory;
    private $dashDirectory;

    public function __construct()
    {
        // Define directories
        $this->uploadDirectory = __DIR__ . '/../../public/uploads/videos/';
        $this->dashDirectory = __DIR__ . '/../../public/uploads/dash/';

        // Create directories if they do not exist
        if (!file_exists($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0777, true);
        }
        if (!file_exists($this->dashDirectory)) {
            mkdir($this->dashDirectory, 0777, true);
        }
    }

    public function uploadVideo(array $file): string
    {
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new \Exception('Error uploading file.');
        }

        // Validate file type
        $allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception('Invalid file type. Only MP4, WebM, and OGG are allowed.');
        }

        // Clean file name: remove spaces and add unique ID
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeFileName = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
        $newFileName = $safeFileName . '_' . uniqid() . '.' . $extension;
        $targetPath = $this->uploadDirectory . $newFileName;

        // Ensure upload directory exists
        if (!is_dir($this->uploadDirectory)) {
            mkdir($this->uploadDirectory, 0777, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('Failed to move uploaded file.');
        }

        return $targetPath;
    }

    public function generateDASH(string $inputFile): string
    {
        if (!file_exists($inputFile)) {
            throw new \Exception('Input file not found.');
        }

        // Generate unique MPD file name
        $fileName = pathinfo($inputFile, PATHINFO_FILENAME);
        $uniqueName = $fileName . '_' . uniqid();
        $mpdFile = $this->dashDirectory . $uniqueName . '.mpd';

        // Ensure DASH directory exists
        if (!is_dir($this->dashDirectory)) {
            mkdir($this->dashDirectory, 0777, true);
        }

        // FFmpeg command to generate DASH
        $ffmpegCommand = sprintf(
            "ffmpeg -i %s -map 0 -c:v libx264 -b:v 1000k -g 48 -sc_threshold 0 -keyint_min 48 -use_timeline 1 -use_template 1 -c:a aac -b:a 128k -f dash %s",
            escapeshellarg($inputFile),
            escapeshellarg($mpdFile)
        );

        exec($ffmpegCommand, $output, $returnVar);

        if ($returnVar !== 0) {
            throw new \Exception('Error generating DASH files.');
        }

        return basename($mpdFile);
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
}