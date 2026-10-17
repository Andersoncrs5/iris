<?php

namespace App\Services\PermissionRequest;

use App\DTOs\PermissionRequest\UpdatePermissionRequestDTO;
use App\Models\PermissionRequestModel;
use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UpdatePermissionRequestUseCase
{
    public function execute(
        UpdatePermissionRequestDTO $dto,
        int $id,
        int $userId,
    ): Result {
        try {
            return DB::transaction(function () use (
                $dto,
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
                    $permissionRequest->getRawOriginal('status')
                    !== PermissionRequestStatus::PENDING->value
                ) {
                    return Result::fail(
                        'Only pending requests can be updated.'
                    );
                }

                $permissionRequest->fill($dto->toArray());
                $permissionRequest->save();

                return Result::ok($permissionRequest);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the permission request.'
            );
        }
    }
}