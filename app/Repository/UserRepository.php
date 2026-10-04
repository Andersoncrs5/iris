<?php

namespace App\Repository;

use App\Models\User;
use App\Repository\Interfaces\IUserRepository;
use App\Utils\Snowflake\IdGeneratorInterface;

class UserRepository implements IUserRepository
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function findByIdForUpdate(int $id): ?User
    {
        return User::where('id', $id)->lockForUpdate()->first();
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function count(): int
    {
        return User::count();
    }

    public function existsById(int $id): bool
    {
        return User::where('id', $id)->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return User::where('email', $email)->exists();
    }

    public function save(User $user): User
    {
        if (!$user->exists && !$user->id) {
            $user->id = $this->idGenerator->generate();
        }

        $user->save();
        return $user;
    }

    public function update(User $user): User
    {
        $user->save();
        return $user;
    }

    public function deleteById(int $id): bool
    {
        return User::destroy($id) > 0;
    }

    public function deleteByIdForce(int $id): bool
    {
        return User::withTrashed()->where('id', $id)->forceDelete() > 0;
    }

    public function restoreById(int $id): bool
    {
        return User::withTrashed()->where('id', $id)->restore() > 0;
    }
}