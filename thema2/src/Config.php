<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;

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

    public function entityManager(): EntityManager
    {
        static $entityManager = null;
        if ($entityManager === null) {
            \Doctrine\DBAL\Types\Type::addType('uuid', \Ramsey\Uuid\Doctrine\UuidType::class);

            $config = ORMSetup::createAttributeMetadataConfiguration(
                paths: [__DIR__],
                isDevMode: true,
            );

            $connection = DriverManager::getConnection(
                [
                    'driver' => 'pdo_mysql',
                    'host' => $this->dbHost,
                    'user' => $this->dbUser,
                    'password' => $this->dbPassword,
                    'dbname' => $this->dbName,
                ],
                $config,
            );

            $entityManager = new EntityManager($connection, $config);
        }
        return $entityManager;
    }
}
