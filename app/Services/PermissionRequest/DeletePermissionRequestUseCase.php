<?php

namespace App\Services\PermissionRequest;

use App\Models\PermissionRequestModel;
use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DeletePermissionRequestUseCase
{
    public function execute(
        int $id,
        int $userId,
    ): Result {
        try {
            return DB::transaction(function () use (
                $id,
                $userId,
            ): Result {
                $permissionRequest = PermissionRequestModel::query()
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->find($id);

                if ($permissionRequest === null) {
                    return Result::fail(
                        'Permission request not found.'
                    );
                }

                if (
                    $permissionRequest->getRawOriginal('status') !== PermissionRequestStatus::PENDING->value
                ) {
                    return Result::fail(
                        'Only pending requests can be deleted.'
                    );
                }

                $permissionRequest->delete();

                return Result::ok($permissionRequest);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while deleting the permission request.'
            );
        }
    }
}