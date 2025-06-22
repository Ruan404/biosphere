<?php

namespace App\File;

use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\Exceptions\NotFoundException;
use Dotenv\Dotenv;
use Exception;
use PDOException;
use function App\Helpers\generateRandomString;
use App\Entities\Layout;
use function App\Helpers\view;


class FileService
{
    private $uploadBaseDir;
    private $tempChunkDir;
    private $uploadSubDir;

    public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $path = $_SERVER["DOCUMENT_ROOT"];

        if (str_ends_with($path, "public")) {
            $path = substr($path, 0, -6);
        }

        $this->uploadBaseDir = realpath($path . $_ENV["UPLOAD_BASE_DIR"]);

        $this->tempChunkDir = $_ENV['TEMP_UPLOAD_DIR'];
        $this->uploadSubDir = $_ENV['UPLOAD_DIR'];
    }
    public function save(string $subDir, string $filename, string $tmp_name)
    {
        $absSubDir = $this->normalizePath("{$this->uploadBaseDir}/{$subDir}");

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!is_dir($absSubDir)) {
            mkdir($absSubDir, 0755, true);
        }

        $filename = generateRandomString() . '.' . $extension;

        if (!move_uploaded_file($tmp_name, $this->normalizePath("{$absSubDir}/{$filename}"))) {
            throw new Exception("Failed to save uploaded file.");
        }

        return $this->normalizePath("/{$subDir}/{$filename}");
    }

    public function chunkedUpload(string $tmp_name, int $chunkNumber, int $totalChunks, string $filename, string $token, array $allowedExtensions, string $uploadSubDir): array
    {
        try {
            $tempDir = $this->normalizePath("{$this->uploadBaseDir}/{$this->tempChunkDir}/{$token}");
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            //valider le fichier
            $this->validate($allowedExtensions, $filename);
            $chunkPath = "{$tempDir}/chunk_{$chunkNumber}";

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
                $finalRelativePath = $this->assembleFile($tempDir, $extension, $uploadSubDir);

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

    private function assembleFile(string $tempDir, string $extension, string $uploadSubDir): string
    {
        try {
            $absUploadSubDir = $this->normalizePath("{$this->uploadBaseDir}/{$uploadSubDir}");

            if (!is_dir($absUploadSubDir)) {
                mkdir($absUploadSubDir, 0755, true);
            }

            $fileName = generateRandomString() . '.' . $extension;

            $finalFile = fopen("{$absUploadSubDir}/{$fileName}", 'wb');

            $chunks = glob("{$tempDir}/chunk_*");
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

            return $this->normalizePath("/{$uploadSubDir}/{$fileName}");

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
        if (!move_uploaded_file($tmp_name, $this->normalizePath("{$this->uploadBaseDir}/{$path}"))) {
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
        $subDirectory = $this->normalizePath("{$this->uploadBaseDir}/{$subDir}");

        if (!is_dir($subDirectory)) {
            mkdir($subDirectory, 0755, true);
        }

        $filename = generateRandomString() . '.' . $extension;

        return $this->normalizePath("/{$subDir}/{$filename}"); //the relative path : /images/file.png
    }

    public function deleteUploadedFile(string $relativePath, int $authorId)
    {
        $absPath = $this->normalizePath("{$this->uploadBaseDir}/{$relativePath}");


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

    public function getValidatedAbsolutePath(string $filename, string $subDir, array $allowedTypes): array
    {
        $file = basename($filename); // prevent directory traversal
        $this->validate($allowedTypes, $file);
       
        $relativePath = $this->normalizePath("/$subDir/$file");
        $absolutePath = $this->normalizePath("{$this->uploadBaseDir}/{$relativePath}");
        
        if (!file_exists($absolutePath)) {
            throw new NotFoundException("File not found.");
        }

        $mime = mime_content_type($absolutePath);

        return [$absolutePath, $mime];
    }

    private function normalizePath($fullPath)
    {
        return str_replace(array(
            "\\\\",
            "\\/",
            "//",
            "\\/",
            "/\\",
            "\\"
        ), "/", $fullPath);
    }

}