<?php
namespace App\Film;

use App\Core\Database;
use App\Exceptions\BadRequestException;
use App\File\FileService;
use App\Film\Dto\FilmAdminPanelDto;
use App\Film\Dto\FilmChunkUploadDto;
use PDO;
use Exception;
use Dotenv\Dotenv;
use PDOException;

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

    public function adminFilms(): ?array
    {
        try {
            $query = Database::getPDO()->query('SELECT  title, token As slug FROM film');

            return $query->fetchAll(PDO::FETCH_CLASS, FilmAdminPanelDto::class);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

    public function upload(FilmChunkUploadDto $data): string
    {
        try {
            $uploadService = new FileService();

            $result = $uploadService->chunkedUpload(
                $data->file["tmp_name"],
                $data->chunkNumber,
                $data->totalChunks,
                $data->filename,
                $data->token,
                ['mp4', 'mov', 'webm']
            );

            if ($result['state'] === 'done' && $data->title && $data->description && $data->coverFile) {
                $uploadService->validate(["jpg", "jpeg", "png"], $data->coverFile["name"]);
                $coverPath = $uploadService->save($_ENV['COVER_DIR'], $data->coverFile["name"], $data->coverFile["tmp_name"]);

                $query = Database::getPDO()->prepare("INSERT INTO film (title, description, file_path, playlist_path, cover_image, token) VALUES (?, ?, ?, ?, ?, ?)");
                $query->execute([$data->title, $data->description, $result['path'], 'playlistPath', $coverPath, $result['token']]);


                if ($query->rowCount() > 0) {
                    return "téléchargement terminé";
                } else {
                    return "le téléchargement a échoué";
                }
            }

            return "chunk $data->chunkNumber téléchargé avec success.";

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

    /**
     * Supprimer un film
     * @param array $video
     * @return bool
     */
    public function deleteFilm(array $video)
    {

        $videoFilePath = $_ENV["UPLOAD_BASE_DIR"] . $video["file_path"];
        if ($videoFilePath && file_exists($videoFilePath)) {
            unlink($videoFilePath);
        }

        $coverFilePath = $_ENV["UPLOAD_BASE_DIR"] . $video["cover_image"];
        if ($coverFilePath && file_exists($coverFilePath)) {
            unlink($coverFilePath);
        }

        $query = Database::getPDO()->prepare("DELETE FROM film WHERE token = ?");
        $query->execute([$video["token"]]);

        return $query->rowCount() > 0;
    }

    /**
     * Supprimer plusieurs films
     * @param array $films
     * @return bool
     */
    public function deleteFilms(array $relative_paths, array $tokens)
    {
        for ($i = 0; $i < count(value: $relative_paths); $i++) {
            $file = $_ENV["UPLOAD_BASE_DIR"] . $relative_paths[$i];
            if ($file && file_exists($file)) {
                unlink($file);
            }
        }

        $in = str_repeat('?,', count($tokens) - 1) . '?';

        $query = Database::getPDO()->prepare("DELETE FROM film WHERE token IN ($in)");
        $query->execute($tokens);

        return $query->rowCount() > 0 ?: throw new BadRequestException("le(s) film(s) n'existent pas");
    }

    public function getFilmsByTokens(array $tokens): ?array
    {
        try {
            $in = str_repeat('?,', count($tokens) - 1) . '?';

            $query = Database::getPDO()->prepare("SELECT file_path, cover_image, token FROM film WHERE token IN ($in)");
            $query->execute($tokens);

            return $query->fetchAll(PDO::FETCH_ASSOC) ?: null;

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception("Something went wrong");
        }
    }

}
