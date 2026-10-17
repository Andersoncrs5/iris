<?php

namespace App\Services\PermissionRequest;

use App\DTOs\PermissionRequest\ChangePermissionRequestStatusDTO;
use App\Models\PermissionRequestModel;
use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ChangePermissionRequestStatusUseCase
{
    public function execute(
        ChangePermissionRequestStatusDTO $dto,
        int $id,
        int $reviewerId,
    ): Result {
        try {
            return DB::transaction(function () use (
                $dto,
                $id,
                $reviewerId,
            ): Result {
                $permissionRequest = PermissionRequestModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($permissionRequest === null) {
                    return Result::fail(
                        'Permission request not found.'
                    );
                }

                $permissionRequest->status = $dto->status->value;

                if (
                    $dto->status === PermissionRequestStatus::PENDING
                ) {
                    $permissionRequest->reviewed_by = null;
                    $permissionRequest->reviewed_at = null;
                    $permissionRequest->review_comment = null;
                } else {
                    $permissionRequest->reviewed_by = $reviewerId;
                    $permissionRequest->reviewed_at = now();
                    $permissionRequest->review_comment = $dto->reviewComment;
                }

                $permissionRequest->save();

                return Result::ok($permissionRequest);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the permission request status.'
            );
        }
    }
}