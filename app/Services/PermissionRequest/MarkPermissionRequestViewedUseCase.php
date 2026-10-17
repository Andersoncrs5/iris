<?php

namespace App\Services\PermissionRequest;

use App\Models\PermissionRequestModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MarkPermissionRequestViewedUseCase
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

                if ($permissionRequest->viewed_at !== null) {
                    return Result::ok($permissionRequest);
                }

                $permissionRequest->viewed_at = now();
                $permissionRequest->save();

                return Result::ok($permissionRequest);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while marking the permission request as viewed.'
            );
        }
    }
}