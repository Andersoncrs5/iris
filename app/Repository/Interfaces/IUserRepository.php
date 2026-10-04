<?php

namespace App\Repository\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface IUserRepository
{
    public function findById(int $id): ?User;
    public function findByIdForUpdate(int $id): ?User;
    public function findByEmail(string $email): ?User;

    public function count(): int;

    public function existsById(int $id): bool;
    public function existsByEmail(string $email): bool;

    public function save(User $user): User;
    public function update(User $user): User;

    public function deleteById(int $id): bool;
    public function deleteByIdForce(int $id): bool;
    public function restoreById(int $id): bool;
}