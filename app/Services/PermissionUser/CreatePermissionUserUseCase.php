<?php

namespace App\Services\PermissionUser;

use App\DTOs\PermissionUser\CreatePermissionUserDTO;
use App\Models\PermissionModel;
use App\Models\PermissionUserModel;
use App\Models\User;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CreatePermissionUserUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator,
    ) {}

    public function execute(CreatePermissionUserDTO $dto): Result
    {
        $actorId = auth()->id();

        if ($actorId === null) {
            return Result::fail(
                'You must be authenticated to assign a permission.'
            );
        }

        try {
            return DB::transaction(function () use ($dto, $actorId): Result {
                $actor = User::query()
                    ->whereKey($actorId)
                    ->lockForUpdate()
                    ->first();

                if ($actor === null) {
                    return Result::fail(
                        'Authenticated user not found.'
                    );
                }

                $targetUser = User::query()
                    ->whereKey($dto->userId)
                    ->lockForUpdate()
                    ->first();

                if ($targetUser === null) {
                    return Result::fail(
                        'The selected user does not exist or has been deleted.'
                    );
                }

                $permission = PermissionModel::query()
                    ->whereKey($dto->permissionId)
                    ->lockForUpdate()
                    ->first();

                if ($permission === null) {
                    return Result::fail(
                        'The selected permission does not exist or has been deleted.'
                    );
                }

                $isSuperAdmin = $actor->isSuperAdmin();

                $isSelf = (string) $actor->getKey()
                    === (string) $targetUser->getKey();

                if ($isSelf && !$isSuperAdmin) {
                    return Result::fail(
                        'You cannot assign permissions to your own account. Only a Super Admin can do this.'
                    );
                }

                $targetIsAdmin = $targetUser->isSuperAdmin()
                    || $targetUser->hasPermissionTo('simple.adm');

                if (!$isSuperAdmin && $targetIsAdmin) {
                    return Result::fail(
                        'Only a Super Admin can assign permissions to administrators.'
                    );
                }

                $isPrivilegedPermission = in_array(
                    $permission->slug,
                    ['super.adm', 'simple.adm', '*'],
                    true
                );

                if (!$isSuperAdmin && $isPrivilegedPermission) {
                    return Result::fail(
                        'Only a Super Admin can grant administrative permissions.'
                    );
                }

                $existingAssignment = PermissionUserModel::withTrashed()
                    ->where('user_id', $targetUser->getKey())
                    ->where('permission_id', $permission->getKey())
                    ->lockForUpdate()
                    ->first();

                if ($existingAssignment !== null) {
                    if ($existingAssignment->trashed()) {
                        return Result::fail(
                            'This permission assignment was previously deleted. Restore the existing assignment instead.'
                        );
                    }

                    return Result::fail(
                        'This user already has this permission.'
                    );
                }

                $permissionUser = PermissionUserModel::query()->create([
                    'id' => $this->idGenerator->generate(),
                    'user_id' => $targetUser->getKey(),
                    'permission_id' => $permission->getKey(),
                    'version' => 1,
                ]);

                $this->addPermissionInUser($permission, $targetUser);

                $permissionUser->load(['user', 'permission']);

                return Result::ok($permissionUser);
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23505') {
                return Result::fail(
                    'This user already has this permission.'
                );
            }

            report($exception);

            return Result::fail(
                'An unexpected database error occurred while assigning the permission.'
            );
        }
    }

    private function addPermissionInUser(
        PermissionModel $permission,
        User $user
    ): void {
        $user->addPermission($permission->slug);
    }
}
