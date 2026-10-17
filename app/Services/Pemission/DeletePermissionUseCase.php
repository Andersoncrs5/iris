<?php

namespace App\Services\Pemission;

use App\Models\PermissionModel;
use App\Models\User;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DeletePermissionUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('permission.delete');

        try {
            return DB::transaction(function () use ($id): Result {
                $permission = PermissionModel::query()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if ($permission === null) {
                    return Result::fail('Permission not found.');
                }

                /*
                 * System module permissions cannot be deleted.
                 */
                if (
                    strcasecmp(
                        trim((string) $permission->module),
                        'system'
                    ) === 0
                ) {
                    return Result::fail(
                        'Permissions belonging to the system module cannot be deleted.'
                    );
                }

                /*
                 * Remove this permission from every user's JSON array.
                 */
                User::query()
                    ->whereJsonContains(
                        'permissions',
                        $permission->slug
                    )
                    ->chunkById(100, function ($users) use ($permission): void {
                        foreach ($users as $user) {
                            $user->removePermission($permission->slug);
                        }
                    });

                /*
                 * Soft-delete the permission.
                 */
                $permission->delete();

                return Result::ok();
            });
        } catch (Throwable $th) {
            report($th);

            return Result::fail(
                'Failed to delete permission.'
            );
        }
    }
}