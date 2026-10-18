<?php

namespace App\Services\Pemission;

use App\DTOs\Permission\UpdatePermissionDTO;
use App\Models\PermissionModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdatePermissionUseCase
{
    public function execute(
        UpdatePermissionDTO $dto,
        int $id
    ): Result {
        if ($id <= 0) {
            return Result::fail(
                'A valid permission ID is required.'
            );
        }

        try {
            return DB::transaction(function () use ($dto, $id): Result {
                $permission = PermissionModel::query()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if ($permission === null) {
                    return Result::fail(
                        'Permission not found.'
                    );
                }

                $permission->fill($dto->toArray());

                $permission->version =
                    (int) $permission->version + 1;

                $permission->save();

                return Result::ok($permission);
            }, 3);
        } catch (QueryException $exception) {
            $sqlState = $exception->errorInfo[0] ?? null;
            $driverCode = (int) ($exception->errorInfo[1] ?? 0);

            if ($sqlState === '23505' || $driverCode === 1062) {
                return Result::fail(
                    'A permission with this name or slug already exists.'
                );
            }

            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the permission.'
            );
        } catch (Throwable $exception) {
            report($exception);

            return Result::fail(
                'An unexpected error occurred while updating the permission.'
            );
        }
    }
}