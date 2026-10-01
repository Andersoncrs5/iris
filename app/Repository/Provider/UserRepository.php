<?php

namespace App\Repository;

use App\Models\UserModel;
use App\Repository\Interfaces\IUserRepository;
use App\Utils\Snowflake\IdGeneratorInterface;

class UserRepository implements IUserRepository
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function findById(int $id): ?UserModel
    {
        return UserModel::find($id);
    }

    public function findByIdForUpdate(int $id): ?UserModel
    {
        return UserModel::where('id', $id)->lockForUpdate()->first();
    }

    public function findByEmail(string $email): ?UserModel
    {
        return UserModel::where('email', $email)->first();
    }

    public function count(): int
    {
        return UserModel::count();
    }

    public function existsById(int $id): bool
    {
        return UserModel::where('id', $id)->exists();
    }

    public function existsByEmail(string $email): bool
    {
        return UserModel::where('email', $email)->exists();
    }

    public function save(UserModel $user): UserModel
    {
        if (!$user->exists && !$user->id) {
            $user->id = $this->idGenerator->generate();
        }

        $user->save();
        return $user;
    }

    public function update(UserModel $user): UserModel
    {
        $user->save();
        return $user;
    }

    public function deleteById(int $id): bool
    {
        return UserModel::destroy($id) > 0;
    }

    public function deleteByIdForce(int $id): bool
    {
        return UserModel::withTrashed()->where('id', $id)->forceDelete() > 0;
    }

    public function restoreById(int $id): bool
    {
        return UserModel::withTrashed()->where('id', $id)->restore() > 0;
    }
}