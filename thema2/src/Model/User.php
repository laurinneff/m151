<?php

namespace App\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User {
	#[ORM\Id]
	#[ORM\Column(type: 'uuid')]
	#[ORM\GeneratedValue(strategy: 'CUSTOM')]
	#[ORM\CustomIdGenerator(class: UuidGenerator::class)]
	private UuidInterface|string $id;
	
	#[ORM\Column(type: 'string')]
	private string $name;

	#[ORM\Column(type: 'string')]
	private string $email;

	#[ORM\Column(type: 'string', name: 'pwd_hash')]
	private string $passwordHash;

	/** @var Collection<int, Account> */
	#[ORM\OneToMany(mappedBy: 'user', targetEntity: Account::class)]
	private Collection $accounts;

	public function getId(): string {
		return $this->id;
	}
	
	public function getName(): string {
		return $this->name;
	}

	public function setName(string $name): void {
		$this->name = $name;
	}

	public function getEmail(): string {
		return $this->email;
	}

	public function setEmail(string $email): void {
		$this->email = $email;
	}

	public function checkPassword(string $password): bool {
		return password_verify($password, $this->passwordHash);
	}

	public function setPassword(string $password): void {
		$this->passwordHash = password_hash($password, PASSWORD_DEFAULT);
	}

	/** @return Collection<int, Account> */
	public function getAccounts(): Collection {
		return $this->accounts;
	}

	public function addAccount(Account $account): void {
		$this->accounts->add($account);
	}

	public function removeAccount(Account $account): void {
		$this->accounts->removeElement($account);
	}
}
