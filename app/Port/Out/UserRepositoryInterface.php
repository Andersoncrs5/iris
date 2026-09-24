<?php

namespace App\Port\Out;

use App\Domain\User\User;

/**
 * @extends GenericRepositoryInterface<User, int>
 */
interface UserRepositoryInterface extends GenericRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function existsByEmail(string $email): bool;
}