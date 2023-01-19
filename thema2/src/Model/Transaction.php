<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'transactions')]
class Transaction {
	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	#[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class: UuidGenerator::class)]
	private UuidInterface|string $id;

	#[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'transactionsOut')]
	private Account $accountFrom;

	#[ORM\ManyToOne(targetEntity: Account::class, inversedBy: 'transactionsIn')]
	private Account $accountTo;

	#[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
	private float $amount;

	#[ORM\Column(type: 'string')]
	private string $description;
	
	#[ORM\Column(type: 'datetime')]
	private \DateTime $timestamp;

	public function __construct() {
		$this->timestamp = new \DateTime();
	}

	public function getId(): string {
		return $this->id;
	}
	
	public function getAccountFrom(): Account {
		return $this->accountFrom;
	}

	public function setAccountFrom(Account $accountFrom): void {
		$this->accountFrom = $accountFrom;
	}

	public function getAccountTo(): Account {
		return $this->accountTo;
	}

	public function setAccountTo(Account $accountTo): void {
		$this->accountTo = $accountTo;
	}

	public function getAmount(): float {
		return $this->amount;
	}

	public function setAmount(float $amount): void {
		$this->amount = $amount;
	}

	public function getDescription(): string {
		return $this->description;
	}

	public function setDescription(string $description): void {
		$this->description = $description;
	}

	public function getTimestamp(): \DateTime {
		return $this->timestamp;
	}

	public function setTimestamp(\DateTime $timestamp): void {
		$this->timestamp = $timestamp;
	}
}
