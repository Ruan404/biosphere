<?php
namespace App\Film;

use App\Core\Database;
use App\Exceptions\BadRequestException;
use PDO;
use Exception;
use Dotenv\Dotenv;
use PDOException;
use function App\Helpers\generateRandomString;

class FilmService
{
    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    public function getAllFilms(): ?array
    {
        try {
            $query = Database::getPDO()->query('SELECT cover_image, token, title, description FROM film');

            return $query->fetchAll(PDO::FETCH_CLASS, Film::class);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function uploadImage(array $coverFile, string $token)
    {
        try {
            $this->validateFile($coverFile, ["jpg", "jpeg", "png"], "cover");

            $coverPath = $_ENV['COVER_DIR'] . $token . '.' . pathinfo($coverFile['name'], PATHINFO_EXTENSION);
            move_uploaded_file($coverFile['tmp_name'], $coverPath);

            return $coverPath;

        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            throw new Exception("cover upload failed");
        }
    }

    private function validateFile(array $file, array $allowedTypes, string $type)
    {
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedTypes)) {
            throw new BadRequestException("Invalid $type file type.");
        }
    }

    public function chunkedUpload(array $videoFile, int $chunkNumber, int $totalChunks, string $filename, string $token): array
    {
        try {
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
                $newToken = generateRandomString();
                $finalPath = $this->assembleFile($tempDir, $newToken);
                return ["state" => "done", "token" => $newToken, "path" => $finalPath];
            }

            return ["state" => "in progress"];

        } catch (Exception $e) {
            $this->cleanChunkFolder($tempDir);
            error_log("Chunk upload error: " . $e->getMessage());
            throw new Exception("video upload failed");
        }
    }

    private function assembleFile($tempDir, $token)
    {
        try {
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
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            throw new Exception("video upload failed");
        }
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

    public function addFilm(string $title, string $description, string $filePath, string $playlistPath, string $coverPath, string $token): bool
    {
        try {
            $query = Database::getPDO()->prepare("INSERT INTO film (title, description, file_path, playlist_path, cover_image, token) VALUES (?, ?, ?, ?, ?, ?)");
            $query->execute([$title, $description, $filePath, $playlistPath, $coverPath, $token]);

            return true;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getFilmByToken($token): ?array
    {
        try {
            if (!$token) {
                throw new BadRequestException("film uri is required");
            }

            $query = Database::getPDO()->prepare("SELECT cover_image, description, token, title, file_path, playlist_path FROM film WHERE token = ?");
            $query->execute([$token]);
            $film = $query->fetch(PDO::FETCH_ASSOC);

            return $film ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function deleteFilm($token)
    {
        $video = $this->getFilmByToken($token);

        $videoFilePath = realpath($video['file_path']);
        if ($videoFilePath && file_exists($videoFilePath)) {
            unlink($videoFilePath);
        }

        $coverFilePath = realpath($video['cover_image']);
        if ($coverFilePath && file_exists($coverFilePath)) {
            unlink($coverFilePath);
        }

        $query = Database::getPDO()->prepare("DELETE FROM film WHERE token = ?");
        $query->execute([$token]);

        return $query->rowCount() > 0;
    }
}
