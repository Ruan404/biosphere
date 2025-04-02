<?php

namespace App\Core;
use Dotenv\Dotenv;
use PDO;

class Database
{
    public static function getPDO(): PDO
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $host = $_ENV["DB_HOST"];
        $user = $_ENV["DB_USER"];
        $pass = $_ENV["DB_PSWD"];
        $dbname = $_ENV["DB_NAME"];

        $pdo = new PDO("mysql:host={$host};dbname={$dbname}", $user, $pass);
        return $pdo;
    }
}