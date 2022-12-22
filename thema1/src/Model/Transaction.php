<?php

declare(strict_types=1);

namespace App\Model;

use App\Attribute\Column;
use App\Attribute\Model;
use DateTime;
use Ramsey\Uuid\Uuid;

#[Model]
class Transaction
{
    #[Column(primaryKey: true)]
    private string $id;

    public function __construct(
        #[Column('account_from')]
        private Account $accountFrom,
        #[Column('account_to')]
        private Account $accountTo,
        #[Column]
        private float $amount,
        #[Column]
        private string $description,
        #[Column]
        private DateTime $timestamp,
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

    public function getAccountFrom(): Account
    {
        return $this->accountFrom;
    }

    public function setAccountFrom(Account $accountFrom): void
    {
        $this->accountFrom = $accountFrom;
    }

    public function getAccountTo(): Account
    {
        return $this->accountTo;
    }

    public function setAccountTo(Account $accountTo): void
    {
        $this->accountTo = $accountTo;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }

    public function setTimestamp(DateTime $timestamp): void
    {
        $this->timestamp = $timestamp;
    }
}
