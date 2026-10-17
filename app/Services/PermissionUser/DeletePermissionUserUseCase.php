<?php

namespace App\Services\PermissionUser;

use App\Models\PermissionUserModel;
use App\Models\User;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DeletePermissionUserUseCase
{
    public function execute(int $id): Result
    {
        $actorId = auth()->id();

        if ($actorId === null) {
            return Result::fail(
                'You must be authenticated to remove a permission.'
            );
        }

        try {
            return DB::transaction(function () use ($id, $actorId): Result {
                $actor = User::query()
                    ->whereKey($actorId)
                    ->lockForUpdate()
                    ->first();

                if ($actor === null) {
                    return Result::fail(
                        'Authenticated user not found.'
                    );
                }

                $permissionUser = PermissionUserModel::query()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if ($permissionUser === null) {
                    return Result::fail(
                        'Permission assignment not found or already deleted.'
                    );
                }

                $targetUser = User::query()
                    ->whereKey($permissionUser->user_id)
                    ->lockForUpdate()
                    ->first();

                if ($targetUser === null) {
                    return Result::fail(
                        'The user associated with this permission no longer exists.'
                    );
                }

                $permission = $permissionUser->permission;

                if ($permission === null) {
                    return Result::fail(
                        'The permission associated with this assignment no longer exists.'
                    );
                }

                $isSuperAdmin = $actor->isSuperAdmin();

                $isSelf = (string) $actor->getKey()
                    === (string) $targetUser->getKey();

                if ($isSelf && !$isSuperAdmin) {
                    return Result::fail(
                        'You cannot remove permissions from your own account.'
                    );
                }

                $targetIsAdmin = $targetUser->isSuperAdmin()
                    || $targetUser->hasPermissionTo('simple.adm');

                if (!$isSuperAdmin && $targetIsAdmin) {
                    return Result::fail(
                        'Only a Super Admin can remove permissions from administrators.'
                    );
                }

                $isPrivilegedPermission = in_array(
                    $permission->slug,
                    ['super.adm', 'simple.adm', '*'],
                    true
                );

                if (!$isSuperAdmin && $isPrivilegedPermission) {
                    return Result::fail(
                        'Only a Super Admin can remove administrative permissions.'
                    );
                }

                $permissionUser->delete();

                $this->removePermissionFromUser($permission, $targetUser);

                return Result::ok($permissionUser);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while removing the permission.'
            );
        }
    }

    private function removePermissionFromUser(
        \App\Models\PermissionModel $permission,
        User $user
    ): void {
        $user->removePermission($permission->slug);
    }
}