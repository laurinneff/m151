<?php

declare(strict_types=1);

namespace App;

readonly class Config
{
    public static function get(): Config
    {
        static $config = null;
        if ($config === null) {
            $config = require_once __DIR__.'/../config.php';
        }
        return $config;
    }

    // Argument order might seem strange, but it's required for named arguments to work while keeping default values working.
    public function __construct(
        public string $jwtKey,
        public string $dbUser,
        public string $dbPassword,
        public string $dbName,
        public string $dbHost = 'localhost',
        public int $dbPort = 3306,
    ) {
    }

    public function db(): \PDO
    {
        static $db = null;
        if ($db === null) {
            $db = new \PDO(
                "mysql:host={$this->dbHost};port={$this->dbPort};dbname={$this->dbName};charset=utf8mb4",
                $this->dbUser,
                $this->dbPassword,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }
        return $db;
    }
}
