<?php

namespace App\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
class Account {
	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	#[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class: UuidGenerator::class)]
	private UuidInterface|string $id;

	#[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'accounts')]
	private User $user;

	#[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
	private float $balance;
	
	#[ORM\Column(type: 'string')]
	private string $name;

	/** @var Collection<int, Transaction> */
	#[ORM\OneToMany(mappedBy: 'accountFrom', targetEntity: Transaction::class)]
	private Collection $transactionsOut;

	/** @var Collection<int, Transaction> */
	#[ORM\OneToMany(mappedBy: 'accountTo', targetEntity: Transaction::class)]
	private Collection $transactionsIn;

	public function getId(): string {
		return $this->id;
	}
	
	public function getUser(): User {
		return $this->user;
	}

	public function setUser(User $user): void {
		$this->user = $user;
	}

	public function getBalance(): float {
		return $this->balance;
	}

	public function setBalance(float $balance): void {
		$this->balance = $balance;
	}

	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): void {
		$this->name = $name;
	}

	/** @return Collection<int, Transaction> */
	public function getTransactionsOut(): Collection {
		return $this->transactionsOut;
	}

	/** @return Collection<int, Transaction> */
	public function getTransactionsIn(): Collection {
		return $this->transactionsIn;
	}

	/** @return Transaction[] */
	public function getTransactions(): array {
		return array_merge($this->transactionsOut->toArray(), $this->transactionsIn->toArray());
	}
}
