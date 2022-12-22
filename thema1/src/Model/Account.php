<?php

declare(strict_types=1);

namespace App\Model;

use App\Attribute\Column;
use App\Attribute\Model;
use Ramsey\Uuid\Uuid;

#[Model]
class Account
{
    #[Column(primaryKey: true)]
    private string $id;

    public function __construct(
        #[Column('user_id')]
        private User $user,
        #[Column]
        private float $balance,
        #[Column]
        private string $name,
        string $id = null,
    ) {
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
