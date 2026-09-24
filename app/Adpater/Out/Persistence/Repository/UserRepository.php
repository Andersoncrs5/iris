<?php

namespace App\Adapter\Out\Persistence\Repository;

use App\Adapter\Out\Persistence\Models\UserModel;
use App\Domain\User\User;
use App\Port\Out\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends GenericEloquentRepository<User, UserModel, int>
 */
class UserRepository extends GenericEloquentRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(UserModel::class);
    }

    /**
     * @param UserModel $model
     */
    protected function toDomain(Model $model): User
    {
        return new User(
            id: $model->id,
            name: $model->name,
            fullName: $model->full_name,
            email: $model->email,
            password: $model->password,
            status: $model->status,
            emailVerifiedAt: $model->email_verified_at ? new \DateTimeImmutable($model->email_verified_at->toDateTimeString()) : null,
            lastLoginAt: $model->last_login_at ? new \DateTimeImmutable($model->last_login_at->toDateTimeString()) : null,
            version: $model->version
        );
    }

    /**
     * @param User $entity
     */
    protected function toDatabase(object $entity): array
    {
        /** @var User $entity */
        return [
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'full_name' => $entity->getFullName(),
            'email' => $entity->getEmail(),
            'password' => $entity->getPassword(),
            'status' => $entity->getStatus(),
            'version' => $entity->getVersion(),
        ];
    }

    public function findByEmail(string $email): ?User
    {
        /** @var UserModel|null $model */
        $model = UserModel::where('email', $email)->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function existsByEmail(string $email): bool
    {
        return UserModel::where('email', $email)->exists();
    }
}