<?php

namespace App\VideoStream;

use App\Attributes\Roles;
use App\VideoStream\VideoStream;
use App\Attributes\Route;
use App\Entities\Role;
class VideoStreamController
{
    #[Roles(array(Role::Admin, Role::User))]
    #[Route("GET", "/stream/[a:file]")]
    public function stream($params)
    {
        $fileName = $params["file"];
        
        $filePath = __DIR__ . '/../../public/uploads/videos/' . basename($fileName) . ".mp4";

        if (!file_exists($filePath)) {
            dd($filePath);
            http_response_code(404);
            echo "File not found.";
            exit;
        }

        $stream = new VideoStream($filePath);
        $stream->start();
    }
}
