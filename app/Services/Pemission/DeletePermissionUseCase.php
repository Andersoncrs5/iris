<?php

namespace App\Services\Pemission;

use App\Models\PermissionModel;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\Gate;

class DeletePermissionUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('permission.delete');

        $permission = PermissionModel::find($id);

        if ($permission === null) {
            return Result::fail("Permission not found");
        }

        try {
            $permission->delete();
            return Result::ok();
        } catch (\Throwable $th) {
            return Result::fail("Failed to delete permission: " . $th->getMessage());
        }
    }
}