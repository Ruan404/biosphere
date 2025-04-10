<?php
namespace App\Film;

use App\Core\Database;
use PDO;
use Exception;
use Dotenv\Dotenv;

class FilmService
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    public function getAllFilms(): ?array
    {
        $query = Database::getPDO()->query('SELECT cover_image, token, title FROM film JOIN genre ON film.genre_id = genre.id');
        return $query->fetchAll(PDO::FETCH_CLASS, Film::class);
    }

    public function getFilmByTitle($title): ?array
    {
        $query = Database::getPDO()->prepare('SELECT cover_image, description, token, title, playlist_path FROM film WHERE film.title = :title');
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_CLASS, Film::class) ?: null;
    }

    public function handleCoverImageUpload($coverFile, $token)
    {
        $this->validateFile($coverFile, ["jpg", "jpeg", "png"], "cover");

        $coverPath = $_ENV['COVER_DIR'] . $token . '.' . pathinfo($coverFile['name'], PATHINFO_EXTENSION);

        move_uploaded_file($coverFile['tmp_name'], $coverPath);

        return $coverPath;
    }

    private function validateFile(array $file, array $allowedTypes, string $type)
    {
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            throw new Exception("Invalid $type file type.");
        }
    }

    public function handleChunkedUpload(array $videoFile, int $chunkNumber, int $totalChunks, string $filename, string $token, array $coverFile): string
    {
        $this->validateFile(["name" => $filename], ["mp4", "mov", "avi"], "video");

        $tempDir = $_ENV['TEMP_UPLOAD_DIR'] . $token;

        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $chunkFilePath = $tempDir . '/chunk_' . $chunkNumber;

        move_uploaded_file($videoFile['tmp_name'], $chunkFilePath);

        // Check if all chunks are uploaded
        $uploadedChunks = glob($tempDir . '/chunk_*');
        if (count($uploadedChunks) === $totalChunks) {
            $finalPath = $this->assembleFile($tempDir, $token);

            if ($finalPath) {
                $coverPath = $this->handleCoverImageUpload($coverFile, $token);
                // Add to DB (can skip or delay until cover is uploaded)
                $title = strtolower(pathinfo($filename, PATHINFO_FILENAME));
                $this->addFilm($title, $finalPath, 'playlistPath', $coverPath, $token);
            }
        }

        return $token;
    }

    private function assembleFile($tempDir, $token)
    {
        $finalFilePath = $_ENV['UPLOAD_DIR'] . $token . '.mp4';
        $finalFile = fopen($finalFilePath, 'wb');

        $chunks = glob($tempDir . '/chunk_*');
        natsort($chunks);

        foreach ($chunks as $chunkFile) {
            $chunk = fopen($chunkFile, 'rb');
            while (!feof($chunk)) {
                fwrite($finalFile, fread($chunk, 1024));
            }
            fclose($chunk);
            unlink($chunkFile);
        }

        fclose($finalFile);
        $this->cleanChunkFolder($tempDir);

        return $finalFilePath;
    }

    private function cleanChunkFolder($tempDir)
    {
        if (!is_dir($tempDir))
            return;

        $files = glob($tempDir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        if (count(glob($tempDir . '/*')) === 0) {
            rmdir($tempDir);
        }
    }

    public function addFilm($title, $filePath, $playlistPath, $coverPath, $token)
    {
        $stmt = Database::getPDO()->prepare("INSERT INTO film (title, file_path, playlist_path, cover_image, token) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $filePath, $playlistPath, $coverPath, $token]);
    }

    public function getFilmByToken($token)
    {
        $stmt = Database::getPDO()->prepare("SELECT cover_image, description, token, title, file_path, playlist_path FROM film WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteFilm($token)
    {
        $video = $this->getFilmByToken($token);

        if (!$video) {
            throw new Exception("Film not found.");
        }

        $videoFilePath = realpath($video['file_path']);
        if ($videoFilePath && file_exists($videoFilePath)) {
            unlink($videoFilePath);
        }

        $coverFilePath = realpath($video['cover_image']);
        if ($coverFilePath && file_exists($coverFilePath)) {
            unlink($coverFilePath);
        }

        $stmt = Database::getPDO()->prepare("DELETE FROM film WHERE token = ?");
        $stmt->execute([$token]);

        return $stmt->rowCount() > 0;
    }
}
