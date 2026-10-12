<?php

namespace App\Services\Pemission;

use App\DTOs\Permission\UpdatePermissionDTO;
use App\Models\PermissionModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;

class UpdatePermissionUseCase
{
    public function execute(
        UpdatePermissionDTO $dto,
        int $id
    ): Result {
        try {
            $permission = PermissionModel::query()->find($id);

            if ($permission === null) {
                return Result::fail(
                    'Permission not found.'
                );
            }

            $permission->fill($dto->toArray());

            $permission->version = (int) $permission->version + 1;

            $permission->save();

            return Result::ok($permission);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23505') {
                return Result::fail(
                    'A permission with this name or slug already exists.'
                );
            }

            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the permission.'
            );
        }
    }
}