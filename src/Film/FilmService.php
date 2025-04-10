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

    public function generateUniqueToken()
    {
        return bin2hex(random_bytes(16)); // Generates a unique token for each video upload
    }

    public function getAllFilms(): ?array
    {
        $query = Database::getPDO()->query('SELECT cover_image, token, title FROM film JOIN genre ON film.genre_id = genre.id');
        $films = $query->fetchAll(PDO::FETCH_CLASS, Film::class);
        return $films;
    }

    public function getFilmByTitle($title): ?array
    {
        $query = Database::getPDO()->prepare('SELECT cover_image, description, token, title, playlist_path FROM film WHERE film.title = :title');
        $query->bindParam(':title', $title, PDO::PARAM_STR);
        $query->execute();
        $film = $query->fetchAll(PDO::FETCH_CLASS, Film::class);
        return $film ?: null;
    }

    public function handleCoverImageUpload($coverFile)
    {
        // Validate and move the cover image
        $this->validateFile($coverFile, ["jpg", "jpeg", "png"], "cover");

        $uniqueToken = $this->generateUniqueToken();

        // Define the cover image path
        $coverPath = $_ENV['COVER_DIR'] . '/' . $uniqueToken . '.' . pathinfo($coverFile['name'], PATHINFO_EXTENSION);

        // Move the cover image to the target directory
        move_uploaded_file($coverFile['tmp_name'], $_ENV['BASE_URL'] . $coverPath);

        return $coverPath;
    }

    // Validate file type
    private function validateFile(array $file, array $allowedTypes, string $type)
    {
        $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedTypes)) {
            throw new Exception("Invalid $type file type.");
        }
    }

    // Handle chunked upload for video
    public function handleChunkedUpload(array $videoFile, int $chunkNumber, int $totalChunks): string
    {
        // Generate unique token for the file upload session
        $uniqueToken = $this->generateUniqueToken();

        // Define the temporary directory for chunks
        $tempDir = $_ENV['TEMP_UPLOAD_DIR'] . '/' . $uniqueToken;

        // Create directory for storing chunks if it does not exist
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        try {
            // Validate video file extension
            $this->validateFile($videoFile, ["mp4", "mov", "avi"], "video");

            // Determine chunk file path
            $chunkFilePath = $tempDir . '/chunk_' . $chunkNumber;

            // Move the uploaded chunk to the temporary storage
            move_uploaded_file($videoFile['tmp_name'], $chunkFilePath);

            // If all chunks are uploaded, assemble the file
            if ($chunkNumber == $totalChunks - 1) {
                $this->assembleFile($tempDir, $uniqueToken);
            }
        } catch (Exception $e) {
            // Clean up any uploaded chunks in case of error
            $this->cleanChunkFolder($tempDir);
            throw $e; // Re-throw the exception to be handled further up
        }

        return $uniqueToken;
    }

    // Assemble video chunks into a single file
    private function assembleFile($tempDir, $uniqueToken)
    {
        // Define the final file path
        $finalFilePath = $_ENV['UPLOAD_DIR'] . '/' . $uniqueToken . '.mp4'; // Or adjust based on the file extension

        // Open the final file for writing
        $finalFile = fopen($finalFilePath, 'wb');

        // Read each chunk and append it to the final file
        foreach (glob($tempDir . '/chunk_*') as $chunkFile) {
            $chunk = fopen($chunkFile, 'rb');
            while ($chunkData = fread($chunk, 1024)) {
                fwrite($finalFile, $chunkData);
            }
            fclose($chunk);
            unlink($chunkFile); // Clean up chunk after appending
        }

        fclose($finalFile);

        // Clean up temporary chunks folder
        $this->cleanChunkFolder($tempDir); // Clean up the chunks folder

        // Store the video details in the database (filename, path, token, etc.)
        $this->addFilm($uniqueToken, $finalFilePath, 'playlistPath', 'coverPath', $uniqueToken);

        return $uniqueToken;
    }

    // Clean up chunk folder after upload is complete
    private function cleanChunkFolder($tempDir)
    {
        // Check if the folder exists
        if (file_exists($tempDir)) {
            // Delete all chunk files inside the folder
            foreach (glob($tempDir . '/chunk_*') as $chunkFile) {
                unlink($chunkFile); // Delete the chunk file
            }

            // Delete the directory itself after cleaning up the chunks
            rmdir($tempDir);
        }
    }

    // Add film record in the database
    public function addFilm($title, $filePath, $playlistPath, $coverPath, $token)
    {
        $stmt = Database::getPDO()->prepare("INSERT INTO film (title, file_path, playlist_path, cover_image, token) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$title, $filePath, $playlistPath, $coverPath, $token]);
    }

    // Get film details by token
    public function getFilmByToken($token)
    {
        $stmt = Database::getPDO()->prepare("SELECT cover_image, description, token, title, file_path, playlist_path FROM film WHERE token = ?");
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete film and its related files from the system and database
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

        // Delete database entry
        $stmt = Database::getPDO()->prepare("DELETE FROM film WHERE token = ?");
        $stmt->execute([$token]);

        return $stmt->rowCount() > 0;
    }
}
