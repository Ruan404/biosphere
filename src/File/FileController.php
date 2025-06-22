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
