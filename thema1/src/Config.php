<?php

declare(strict_types=1);

namespace App;

class Config
{
    public static function getConfig(): Config
    {
        static $config = null;
        if ($config === null) {
            $config = require_once __DIR__.'/../config.php';
        }
        return $config;
    }

    // Argument order might seem strange, but it's required for named arguments to work while keeping default values working.
    public function __construct(
        private string $dbUser,
        private string $dbPassword,
        private string $dbName,
        private string $dbHost = 'localhost',
        private int $dbPort = 3306,
    ) {
    }

    public function getDbHost(): string
    {
        return $this->dbHost;
    }

    public function getDbPort(): int
    {
        return $this->dbPort;
    }

    public function getDbUser(): string
    {
        return $this->dbUser;
    }

    public function getDbPassword(): string
    {
        return $this->dbPassword;
    }

    public function getDbName(): string
    {
        return $this->dbName;
    }

    public function getDb(): \PDO
    {
        static $db = null;
        if ($db === null) {
            $db = new \PDO(
                "mysql:host={$this->getDbHost()};port={$this->getDbPort()};dbname={$this->getDbName()};charset=utf8mb4",
                $this->getDbUser(),
                $this->getDbPassword(),
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                ]
            );
        }
        return $db;
    }
}
