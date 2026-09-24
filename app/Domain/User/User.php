<?php

namespace App\Domain\User;

class User
{
    public function __construct(
        private readonly ?int $id,
        private string $name,
        private string $fullName,
        private string $email,
        private string $password,
        private string $status = 'ACTIVE',
        private ?\DateTimeImmutable $emailVerifiedAt = null,
        private ?\DateTimeImmutable $lastLoginAt = null,
        private int $version = 1
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getFullName(): string { return $this->fullName; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getStatus(): string { return $this->status; }
    public function getVersion(): int { return $this->version; }

    public function activate(): void
    {
        $this->status = 'ACTIVE';
        $this->version++;
    }

    public function suspend(): void
    {
        $this->status = 'SUSPENDED';
        $this->version++;
    }

    public function recordLogin(): void
    {
        $this->lastLoginAt = new \DateTimeImmutable();
    }
}