<?php

namespace App\VideoStream;

use App\Attributes\Roles;
use App\Entities\Layout;
use App\VideoStream\VideoStream;
use App\Attributes\Route;
use App\Entities\Role;
use function App\Helpers\view;
class VideoStreamController
{
    #[Roles(array(Role::Admin, Role::User))]
    #[Route("GET", "/stream/[*:file]")]
    public function stream($params)
    {
        if (empty($_SERVER['HTTP_RANGE'])) {
            return view("/errors/404", Layout::Error);
        }

        if(preg_match("/^[a-zA-Z0-9]+\.(?:mp4|mov|avi)$/", $params['file'])){
            $fileName = $params["file"];
           
            $filePath = __DIR__ . '/../../public/uploads/videos/' . basename($fileName);
    
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
