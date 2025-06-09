<?php

namespace App\VideoStream;

use App\Entities\Layout;
use App\VideoStream\VideoStream;
use App\Attributes\Route;
use Dotenv\Dotenv;
use function App\Helpers\view;

class VideoStreamController
{
     public function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
    }

    #[Route("GET", "/stream/[*:file]")]
    public function stream($params)
    {
        if (empty($_SERVER['HTTP_RANGE'])) {
            return view("/errors/404", Layout::Error);
        }

        if(preg_match("/^[a-zA-Z0-9]+\.(?:mp4|mov|avi)$/", $params['file'])){
            $fileName = $params["file"];
           
            $filePath = __DIR__ . '/../../uploads/videos/' . basename($fileName);
    
            if (!file_exists($filePath)) {
                http_response_code(404);
                echo "File not found.";
                exit;
            }
    
            $stream = new VideoStream($filePath);
            $stream->start();
        }
        else{
            return view("/errors/404", Layout::Error);
        }

    }
}
