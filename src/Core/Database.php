<?php

namespace App\Core;

use Dotenv\Dotenv;
use PDO;

class Database
{
    public static string $host;
    public static string $user;
    public static string $pass;
    public static string $dbname;
    public static string $port = '3306'; // default

    private static bool $loaded = false;

    private static function loadEnv(): void
    {
        if (!self::$loaded) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->load();

            self::$host = $_ENV["DB_HOST"];
            self::$user = $_ENV["DB_USER"];
            self::$pass = $_ENV["DB_PSWD"];
            self::$dbname = $_ENV["DB_NAME"];
            self::$port = $_ENV["DB_PORT"] ?? '3306';

            self::$loaded = true;
        }
    }

    public static function getPDO(): PDO
    {
        self::loadEnv();

        $dsn = "mysql:host=" . self::$host . ";dbname=" . self::$dbname . ";charset=utf8mb4";
        
        return new PDO($dsn, self::$user, self::$pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }

    public static function getDBConfig(): array
    {
        self::loadEnv();

        return [
            'type'     => 'mysql',
            'hostname' => self::$host,
            'username' => self::$user,
            'password' => self::$pass,
            'database' => self::$dbname,
            'hostport' => self::$port,
        ];
    }
}