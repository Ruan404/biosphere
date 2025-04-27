<?php

namespace App\Core\Middleware;

class IsLoggedIn
{
    public function handle()
    {
    
        if (session_status() === 1) {
            session_start();
        }
  
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Allow login page without session
        if ($currentUri === '/login') {
            return;
        }
        if (empty($_SESSION)) {
            header('Location: /login');
            exit;
        }
    }
}
