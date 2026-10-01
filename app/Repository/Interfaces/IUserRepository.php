<?php

namespace App\Repository\Interfaces;

use App\Models\UserModel;
use Illuminate\Database\Eloquent\Collection;

interface IUserRepository
{
    public function findById(int $id): ?UserModel;
    public function findByIdForUpdate(int $id): ?UserModel;
    public function findByEmail(string $email): ?UserModel;

    public function count(): int;

    public function existsById(int $id): bool;
    public function existsByEmail(string $email): bool;

    public function save(UserModel $user): UserModel;
    public function update(UserModel $user): UserModel;

    public function deleteById(int $id): bool;
    public function deleteByIdForce(int $id): bool;
    public function restoreById(int $id): bool;
}