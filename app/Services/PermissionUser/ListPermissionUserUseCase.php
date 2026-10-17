<?php

namespace App\Services\PermissionUser;

use App\DTOs\PermissionUser\FilterPermissionUserDTO;
use App\Models\PermissionUserModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPermissionUserUseCase
{
    public function execute(
        FilterPermissionUserDTO $dto
    ): LengthAwarePaginator {
        $query = PermissionUserModel::query()
            ->with(['user', 'permission']);

        if ($dto->id !== null) {
            $query->where('id', $dto->id);
        }

        if ($dto->userId !== null) {
            $query->where('user_id', $dto->userId);
        }

        if (
            $dto->userName !== null
            || $dto->userEmail !== null
        ) {
            $query->whereHas('user', function (Builder $userQuery) use ($dto) {
                $this->applyTextFilter(
                    $userQuery,
                    'name',
                    $dto->userName,
                    $dto->userNameIgnoreCase
                );

                $this->applyTextFilter(
                    $userQuery,
                    'email',
                    $dto->userEmail,
                    $dto->userEmailIgnoreCase
                );
            });
        }

        if ($dto->permissionId !== null) {
            $query->where('permission_id', $dto->permissionId);
        }

        if (
            $dto->permissionName !== null
            || $dto->permissionSlug !== null
            || $dto->permissionModule !== null
        ) {
            $query->whereHas(
                'permission',
                function (Builder $permissionQuery) use ($dto) {
                    $this->applyTextFilter(
                        $permissionQuery,
                        'name',
                        $dto->permissionName,
                        $dto->permissionNameIgnoreCase
                    );

                    $this->applyTextFilter(
                        $permissionQuery,
                        'slug',
                        $dto->permissionSlug,
                        $dto->permissionSlugIgnoreCase
                    );

                    if ($dto->permissionModule !== null) {
                        $permissionQuery->where(
                            'module',
                            $dto->permissionModule
                        );
                    }
                }
            );
        }

        if ($dto->versionMin !== null) {
            $query->where('version', '>=', $dto->versionMin);
        }

        if ($dto->versionMax !== null) {
            $query->where('version', '<=', $dto->versionMax);
        }

        if ($dto->createdAtFrom !== null) {
            $query->whereDate(
                'created_at',
                '>=',
                $dto->createdAtFrom
            );
        }

        if ($dto->createdAtTo !== null) {
            $query->whereDate(
                'created_at',
                '<=',
                $dto->createdAtTo
            );
        }

        if ($dto->updatedAtFrom !== null) {
            $query->whereDate(
                'updated_at',
                '>=',
                $dto->updatedAtFrom
            );
        }

        if ($dto->updatedAtTo !== null) {
            $query->whereDate(
                'updated_at',
                '<=',
                $dto->updatedAtTo
            );
        }

        match ($dto->trashed) {
            'with' => $query->withTrashed(),
            'only' => $query->onlyTrashed(),
            default => null,
        };

        $sortableColumns = [
            'id',
            'user_id',
            'permission_id',
            'version',
            'created_at',
            'updated_at',
            'deleted_at',
        ];

        $sortBy = in_array($dto->sortBy, $sortableColumns, true)
            ? $dto->sortBy
            : 'created_at';

        $sortOrder = in_array($dto->sortOrder, ['asc', 'desc'], true)
            ? $dto->sortOrder
            : 'desc';

        return $query
            ->orderBy($sortBy, $sortOrder)
            ->paginate($dto->perPage)
            ->withQueryString();
    }

    private function applyTextFilter(
        Builder $query,
        string $column,
        ?string $value,
        bool $ignoreCase
    ): void {
        if ($value === null || trim($value) === '') {
            return;
        }

        $operator = $ignoreCase ? 'ILIKE' : 'LIKE';

        $query->where(
            $column,
            $operator,
            '%' . trim($value) . '%'
        );
    }
}