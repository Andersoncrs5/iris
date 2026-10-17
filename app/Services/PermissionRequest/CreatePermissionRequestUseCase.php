<?php

namespace App\Services\PermissionRequest;

use App\DTOs\PermissionRequest\CreatePermissionRequestDTO;
use App\Models\PermissionModel;
use App\Models\PermissionRequestModel;
use App\Models\PermissionUserModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreatePermissionRequestUseCase
{
    public function execute(
        CreatePermissionRequestDTO $dto,
    ): Result {
        Gate::authorize('permission.request.create');

        $userId = auth()->id();

        if ($userId === null) {
            return Result::fail(
                'You must be authenticated to create a permission request.'
            );
        }

        try {
            return DB::transaction(function () use ($dto, $userId): Result {
                $permission = PermissionModel::query()
                    ->find($dto->permissionId);

                if ($permission === null) {
                    return Result::fail('Permission not found.');
                }

                $user = auth()->user();

                if ($user === null) {
                    return Result::fail(
                        'You must be authenticated to create a permission request.'
                    );
                }

                // Super admins already have access to all permissions.
                if ($user->isSuperAdmin()) {
                    return Result::fail(
                        'You already have access to this permission. No request is necessary.'
                    );
                }

                // Check permissions stored in the user's JSON column.
                $hasPermissionInJson = is_array($user->permissions)
                    && in_array($permission->slug, $user->permissions, true);

                // Check active assignments in the permission_user table.
                // The PermissionUserModel uses SoftDeletes.
                $hasPermissionInPivot = PermissionUserModel::query()
                    ->where('user_id', $userId)
                    ->where('permission_id', $permission->id)
                    ->exists();

                if ($hasPermissionInJson || $hasPermissionInPivot) {
                    return Result::fail(
                        'You already have this permission. No request is necessary.'
                    );
                }

                $permissionRequest = PermissionRequestModel::query()->create([
                    'user_id' => $userId,
                    ...$dto->toArray(),
                ]);

                return Result::ok($permissionRequest);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while creating the permission request.'
            );
        }
    }
}