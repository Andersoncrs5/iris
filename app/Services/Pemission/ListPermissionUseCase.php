<?php

namespace App\Services\Pemission;

use App\DTOs\Permission\FilterPermissionDTO;
use App\Models\PermissionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPermissionUseCase
{
    public function execute(
        FilterPermissionDTO $dto
    ): LengthAwarePaginator {
        $query = PermissionModel::query();

        // Soft deletes
        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        // Name filter
        if ($dto->name !== null && $dto->name !== '') {
            if ($dto->nameIgnoreCase) {
                $query->where(
                    'name',
                    'ilike',
                    '%' . $dto->name . '%'
                );
            } else {
                $query->where(
                    'name',
                    'like',
                    '%' . $dto->name . '%'
                );
            }
        }

        // Slug filter
        if ($dto->slug !== null && $dto->slug !== '') {
            if ($dto->slugIgnoreCase) {
                $query->where(
                    'slug',
                    'ilike',
                    '%' . $dto->slug . '%'
                );
            } else {
                $query->where(
                    'slug',
                    'like',
                    '%' . $dto->slug . '%'
                );
            }
        }

        // Module filter
        if ($dto->module !== null && $dto->module !== '') {
            if ($dto->moduleIgnoreCase) {
                $query->where(
                    'module',
                    'ilike',
                    '%' . $dto->module . '%'
                );
            } else {
                $query->where(
                    'module',
                    'like',
                    '%' . $dto->module . '%'
                );
            }
        }

        // Description filter
        if (
            $dto->description !== null
            && $dto->description !== ''
        ) {
            $query->where(
                'description',
                'ilike',
                '%' . $dto->description . '%'
            );
        }

        // Sorting
        $query->orderBy(
            $dto->sortBy->value,
            $dto->sortOrder
        );

        // Pagination
        return $query->paginate($dto->perPage);
    }
}