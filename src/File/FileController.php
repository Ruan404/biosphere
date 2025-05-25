<?php

namespace App\File;

use App\Attributes\Route;
use App\Entities\Layout;
use function App\Helpers\view;

class FileController
{
    #[Route('GET', '/images/[*:filename]')]
    public function getImage($params)
    {
        $filename = htmlspecialchars($params["filename"]);
        $imagePath = __DIR__ . '/../../uploads/images/' . $filename;
        
        if (file_exists($imagePath)) {
            // Determine the content type
            $mimeType = mime_content_type($imagePath);

            // Set the appropriate headers
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($imagePath));

            // Output the image
            readfile($imagePath);
        } else {
           return view("/errors/404", Layout::Error);
        }
    }
}