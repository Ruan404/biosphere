<?php

namespace App\Film;
use App\Core\Database;
use PDO;
use Exception;
use Dotenv\Dotenv;
ini_set('max_execution_time', 300);

class FilmService
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    public function generateUniqueToken()
    {
        return bin2hex(random_bytes(16));
    }

    public function processHLS($inputFile, $outputFolder)
    {
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
        $query = Database::getPDO()->query('SELECT cover_image,token, title FROM film JOIN genre ON film.genre_id = genre.id');
        $films = $query->fetchAll(PDO::FETCH_CLASS, Film::class);
        return $films;
    }

    public function getFilmByTitle($title): ?array
    {
        $query = Database::getPDO()->prepare('SELECT cover_image,description, token, title, playlist_path FROM film WHERE film.title = :title');
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->execute();
        $film = $query->fetchAll(PDO::FETCH_CLASS, Film::class);

        if ($film) {
            return $film;
        }

        return null;
    }

    private function validateFile(array $file, array $allowedTypes, string $type)
    {
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            throw new Exception("Invalid $type file type.");
        }
    }

    public function handleFilmUpload(array $videoFile, array $coverFile): string
    {
        // Validate files
        $this->validateFile($videoFile, ["mp4", "mov", "avi"], "video");
        $this->validateFile($coverFile, ["jpg", "jpeg", "png"], "cover");

        // Generate unique token
        $uniqueToken = $this->generateUniqueToken();

        // Define file paths
        $videoPath = $_ENV['UPLOAD_DIR'] . $uniqueToken . "." . pathinfo($videoFile["name"], PATHINFO_EXTENSION);
        $coverPath = $_ENV['COVER_DIR'] . $uniqueToken . "." . pathinfo($coverFile["name"], PATHINFO_EXTENSION);
        $hlsFolder = $_ENV['HLS_DIR'] . $uniqueToken;

        // Move files
        move_uploaded_file($videoFile["tmp_name"], $_ENV['BASE_URL'] . $videoPath);
        move_uploaded_file($coverFile["tmp_name"], $_ENV['BASE_URL'] . $coverPath);

        // Process HLS
        $playlistPath = $this->processHLS($_ENV['BASE_URL'] . $videoPath, $_ENV['BASE_URL'] . $hlsFolder);
        $playlistPath = str_replace($_ENV['BASE_URL'], '', $playlistPath); // Store relative path

        // Store in database
        $title = strtolower(pathinfo($videoFile["name"], PATHINFO_FILENAME));
        $this->addFilm($title, $videoPath, $playlistPath, $coverPath, $uniqueToken);

        return $uniqueToken;
    }

    public function addFilm($title, $filePath, $playlistPath, $coverPath, $token)
    {
        $stmt = Database::getPDO()->prepare("INSERT INTO film (title, file_path, playlist_path, cover_image, token) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $filePath, $playlistPath, $coverPath, $token]);
    }

    public function getFilmByToken($token)
    {
        $stmt = Database::getPDO()->prepare("SELECT cover_image,description, token, title,file_path, playlist_path FROM film WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function deleteFilm($token)
    {
        // Get video details from DB
        $video = $this->getFilmByToken($token);

        if (!$video) {
            throw new Exception("Film not found.");
        }
    
        // Remove the video file
        $videoFilePath = realpath($video['file_path']);
       
        
        if (file_exists($videoFilePath)) {
            unlink($videoFilePath);
        }
    
        // Remove the cover image
        $coverFilePath = realpath($video['cover_image']);
        if (!empty($video['cover_image']) && file_exists($coverFilePath)) {
            unlink($coverFilePath);
        }
    
        // Remove the HLS folder and all its contents
        $hlsFolder = $_ENV['HLS_DIR'] . $token;
        if (is_dir($hlsFolder)) {
            foreach (glob("$hlsFolder/*") as $file) {
                unlink($file);
            }
            rmdir($hlsFolder);
        }
    
        // Delete database entry
        $stmt = Database::getPDO()->prepare("DELETE FROM film WHERE token = ?");
        $stmt->execute([$token]);
    
        return true;
    }
    

}