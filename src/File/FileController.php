<?php

namespace App\File;

use App\Entities\Layout;
use App\Attributes\Route;
use App\Exceptions\HttpExceptionInterface;
use function App\Helpers\view;

class FileController
{
    private FileService $fileService;

    public function __construct()
    {
        $this->fileService = new FileService();
    }

    #[Route('GET', '/images/{file}')]
    public function getImage($request)
    {
        try {
            [$path, $mime] = $this->fileService->getValidatedAbsolutePath(
                $request->getAttribute("file"),
                'images',
                ['jpg', 'jpeg', 'png']
            );
        } catch (HttpExceptionInterface) {
            return view("/errors/404", Layout::Error);
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    #[Route('GET', '/avatars/{file}')]
    public function getAvatar($request)
    {
        try {
            $file = $request->getAttribute("file");
           
            [$path, $mime] = $this->fileService->getValidatedAbsolutePath(
                $file,
                'avatars',
                ['jpg', 'jpeg', 'png']
            );
        } catch (HttpExceptionInterface) {
            return view("/errors/404", Layout::Error);
        }

        // Get last modified time
        $lastModified = filemtime($path);
        $etag = md5_file($path);

        // Handle browser cache
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=31536000'); // 1 year
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        header('ETag: "' . $etag . '"');

        // 304 Not Modified support
        if (
            isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) &&
            strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) === $lastModified
        ) {
            http_response_code(304);
            exit;
        }

        if (
            isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
            trim($_SERVER['HTTP_IF_NONE_MATCH']) === '"' . $etag . '"'
        ) {
            http_response_code(304);
            exit;
        }

        readfile($path);
        exit;
    }

    #[Route('GET', '/videos/{file}')]
    public function getVideo($request)
    {
        try {
            [$path, $mime] = $this->fileService->getValidatedAbsolutePath(
                $request->getAttribute("file"),
                'videos',
                ['mp4', 'mov', 'avi']
            );
        } catch (HttpExceptionInterface) {
            return view("/errors/404", Layout::Error);
        }

        $stream = new FileStream($path, $mime);
        $stream->start(); // Handles headers, range, output
    }

    #[Route('GET', '/audios/{file}')]
    public function getAudio($request)
    {
        try {
            [$path, $mime] = $this->fileService->getValidatedAbsolutePath(
                $request->getAttribute("file"),
                'audios',
                ['mp3']
            );
        } catch (HttpExceptionInterface) {
            return view("/errors/404", Layout::Error);
        }

        $stream = new FileStream($path, $mime);
        $stream->start();
    }

}
