<?php

namespace App\Services\PermissionUser;

use App\Models\PermissionUserModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class RestorePermissionUserUseCase
{
    public function execute(int $id): Result
    {
        try {
            return DB::transaction(function () use ($id): Result {
                $permissionUser = PermissionUserModel::withTrashed()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if ($permissionUser === null) {
                    return Result::fail(
                        'Permission assignment not found.'
                    );
                }

                if (!$permissionUser->trashed()) {
                    return Result::fail(
                        'This permission assignment is not deleted.'
                    );
                }

                $permissionUser->restore();

                return Result::ok($permissionUser);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while restoring the permission assignment.'
            );
        }
    }
}