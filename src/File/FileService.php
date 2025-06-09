<?php

namespace App\File;

use App\Core\Database;
use App\Exceptions\BadRequestException;
use Dotenv\Dotenv;
use Exception;
use PDOException;
use function App\Helpers\generateRandomString;

class FileService
{
    private $uploadBaseDir;
    private $tempChunkDir;
    private $uploadSubDir;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->uploadBaseDir = $_SERVER["DOCUMENT_ROOT"]."/../".$_ENV["UPLOAD_BASE_DIR"];
        $this->tempChunkDir = $_ENV['TEMP_UPLOAD_DIR'];
        $this->uploadSubDir = $_ENV['UPLOAD_DIR'];
    }

    public function save(string $subDir, string $filename, string $tmp_name)
    {
        $absSubDir = $this->uploadBaseDir . $subDir;

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!is_dir($absSubDir)) {
            mkdir($absSubDir, 0755, true);
        }

        $filename = generateRandomString() . '.' . $extension;

        $path = $subDir . '/' . $filename;

        if (!move_uploaded_file($tmp_name, $this->uploadBaseDir . $path)) {
            throw new Exception("Failed to save uploaded file.");
        }

        return preg_replace("#/{2,}#", "/", $path);

    }

    public function chunkedUpload(string $tmp_name, int $chunkNumber, int $totalChunks, string $filename, string $token, array $allowedExtensions): array
    {
        try {
            $tempDir = "{$this->uploadBaseDir}{$this->tempChunkDir}{$token}";
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            //valider le fichier
            $this->validate($allowedExtensions, $filename);
            $chunkPath = $tempDir . "/chunk_" . $chunkNumber;

            //créer le sous dossier s'il n'existe pas
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            //enregister les chunks dans un emplacement temporaire
            if (!move_uploaded_file($tmp_name, $chunkPath)) {
                throw new Exception("Failed to save uploaded file.");
            }

            // vérifier que tous les chunks ont été uploadés
            $uploadedChunks = glob($tempDir . '/chunk_*');

            if (count($uploadedChunks) === $totalChunks) {
                //assembler le fichier
                $finalRelativePath = $this->assembleFile($tempDir, $extension);

                return ["state" => "done", "token" => generateRandomString(), "path" => $finalRelativePath];
            }

            return ["state" => "in progress"];

        } catch (Exception $e) {
            $this->cleanChunkFolder($tempDir);
            error_log("Chunk upload error: " . $e->getMessage());
            throw new Exception("video upload failed");
        }
    }

    public function validate(array $allowedTypes, string $filename)
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedTypes)) {
            throw new BadRequestException("Invalid file type.");
        }
    }

    private function assembleFile(string $tempDir, string $extension): string
    {
        try {
            $absUploadSubDir = $this->uploadBaseDir . $this->uploadSubDir;

            if (!is_dir($absUploadSubDir)) {
                mkdir($absUploadSubDir, 0755, true);
            }

            $finalRelativePath = $this->uploadSubDir . generateRandomString() . '.' . $extension;

            $finalFile = fopen($this->uploadBaseDir . $finalRelativePath, 'wb');

            $chunks = glob($tempDir . '/chunk_*');
            natsort($chunks);

            foreach ($chunks as $chunk) {
                $in = fopen($chunk, 'rb');
                while (!feof($in)) {
                    fwrite($finalFile, fread($in, 8192));
                }
                fclose($in);
                unlink($chunk);
            }

            fclose($finalFile);
            $this->cleanChunkFolder($tempDir);

            return $finalRelativePath;

        } catch (Exception $e) {
            $this->cleanChunkFolder($tempDir);
            error_log("Failed to assemble file on chunk upload: " . $e->getMessage());
            throw new Exception("video upload failed");
        }
    }

    private function cleanChunkFolder(string $tempDir): void
    {
        if (!is_dir($tempDir)) {
            return;
        }

        $files = glob($tempDir . '/*');
        $count = count($files);

        for ($i = 0; $i < $count; $i++) {
            if (is_file($files[$i])) {
                unlink($files[$i]);
            }
        }

        // Remove directory only if empty
        if (count(glob($tempDir . '/*')) === 0) {
            rmdir($tempDir);
        }
    }

    public function uploadAndSaveFile(string $tmp_name, string $name, string $path, string $size, string $type, int $authorId)
    {
        if (!move_uploaded_file($tmp_name, $this->uploadBaseDir . $path)) {
            throw new Exception("Failed to save uploaded file.");
        }

        try {
            //save to db
            $query = Database::getPDO()->prepare("INSERT INTO uploaded_files(filename, path, size, mime_type, type, created_by) VALUES(?,?,?,?,?,?)");
            $query->execute([$name, $path, $size, $type, $type, $authorId]);

            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error on insert file query: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function generatePath(string $filename, string $subDir)
    {

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $subDirectory = $this->uploadBaseDir . $subDir;

        if (!is_dir($subDirectory)) {
            mkdir($subDirectory, 0755, true);
        }

        $filename = generateRandomString() . '.' . $extension;

        $relativePath = $subDir . '/' . $filename;

        return preg_replace("#/{2,}#", "/", $relativePath);
    }

    public function deleteUploadedFile(string $relativePath, int $authorId)
    {
        $path = preg_replace("#/{2,}#", "/", $relativePath);
        $absPath = $this->uploadBaseDir . $path;

        if (file_exists($absPath)) {
            unlink($absPath);
        }

        try {
            //delete from the db
            $query = Database::getPDO()->prepare("DELETE FROM uploaded_files WHERE created_by = ?");
            $query->execute([$authorId]);

            return $query->rowCount() > 0;

        } catch (PDOException $e) {
            error_log("Error on delete uploaded file query:" . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function getUploadedFileByAuthorId($authorId)
    {
        try {
            $query = Database::getPDO()->prepare(query: "SELECT * FROM uploaded_files WHERE created_by = ?");
            $query->execute([$authorId]);
            $uploadfile = $query->fetch();

            return $uploadfile;

        } catch (PDOException $e) {
            error_log("Error on select uploaded file query:" . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }
}