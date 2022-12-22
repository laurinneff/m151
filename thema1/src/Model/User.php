<?php

declare(strict_types=1);

namespace App\Model;

use App\Attribute\Model;
use App\Attribute\Column;
use Ramsey\Uuid\Uuid;

#[Model]
class User
{
    #[Column(primaryKey: true)]
    private string $id;

        #[Column('pwd_hash')]
        private string $passwordHash;

    public function __construct(
        #[Column]
        private string $name,
        #[Column]
        private string $email,
        string $password,
        string $id = null,
    ) {
        $this->setPassword($password);
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function setPassword(string $password): void
    {
        $this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
    }
}
