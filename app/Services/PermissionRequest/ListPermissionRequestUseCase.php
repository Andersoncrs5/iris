<?php

namespace App\Services\PermissionRequest;

use App\DTOs\PermissionRequest\FilterPermissionRequestDTO;
use App\Models\PermissionRequestModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListPermissionRequestUseCase
{
    public function execute(
        FilterPermissionRequestDTO $dto,
    ): LengthAwarePaginator {
        $query = PermissionRequestModel::query()
            ->with(['user', 'permission', 'reviewer']);

        if ($dto->userId !== null) {
            $query->where('user_id', $dto->userId);
        }

        // Request owner's name
        if ($dto->userName !== null) {
            $query->whereHas(
                'user',
                function (Builder $userQuery) use ($dto): void {
                    $this->applyTextFilter(
                        $userQuery,
                        'name',
                        $dto->userName,
                        $dto->userNameIgnoreCase,
                    );
                }
            );
        }

        // Request owner's email
        if ($dto->userEmail !== null) {
            $query->whereHas(
                'user',
                function (Builder $userQuery) use ($dto): void {
                    $this->applyTextFilter(
                        $userQuery,
                        'email',
                        $dto->userEmail,
                        $dto->userEmailIgnoreCase,
                    );
                }
            );
        }

        // Permission ID
        if ($dto->permissionId !== null) {
            $query->where('permission_id', $dto->permissionId);
        }

        // Permission name
        if ($dto->permissionName !== null) {
            $query->whereHas(
                'permission',
                function (Builder $permissionQuery) use ($dto): void {
                    $this->applyTextFilter(
                        $permissionQuery,
                        'name',
                        $dto->permissionName,
                        $dto->permissionNameIgnoreCase,
                    );
                }
            );
        }

        // Permission slug
        if ($dto->permissionSlug !== null) {
            $query->whereHas(
                'permission',
                function (Builder $permissionQuery) use ($dto): void {
                    $this->applyTextFilter(
                        $permissionQuery,
                        'slug',
                        $dto->permissionSlug,
                        $dto->permissionSlugIgnoreCase,
                    );
                }
            );
        }

        // One or multiple statuses
        if ($dto->statuses !== []) {
            $query->whereIn('status', $dto->statuses);
        }

        // Request justification
        if ($dto->reason !== null) {
            $this->applyTextFilter(
                $query,
                'reason',
                $dto->reason,
                $dto->reasonIgnoreCase,
            );
        }

        // Reviewer ID
        if ($dto->reviewedBy !== null) {
            $query->where('reviewed_by', $dto->reviewedBy);
        }

        // Review comment
        if ($dto->reviewComment !== null) {
            $this->applyTextFilter(
                $query,
                'review_comment',
                $dto->reviewComment,
                $dto->reviewCommentIgnoreCase,
            );
        }

        // Viewed / not viewed
        if ($dto->viewed === true) {
            $query->whereNotNull('viewed_at');
        } elseif ($dto->viewed === false) {
            $query->whereNull('viewed_at');
        }

        // Creation date range
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

        // Review date range
        if ($dto->reviewedAtFrom !== null) {
            $query->whereDate(
                'reviewed_at',
                '>=',
                $dto->reviewedAtFrom
            );
        }

        if ($dto->reviewedAtTo !== null) {
            $query->whereDate(
                'reviewed_at',
                '<=',
                $dto->reviewedAtTo
            );
        }

        // Sorting: sortBy was validated against an allowlist in the DTO.
        $query->orderBy(
            $dto->sortBy,
            $dto->sortOrder
        );

        // Pagination
        return $query
            ->paginate($dto->perPage)
            ->withQueryString();
    }

    /**
     * Apply a case-sensitive or case-insensitive text filter.
     *
     * PostgreSQL supports LIKE and ILIKE.
     *
     * @param Builder<PermissionRequestModel> $query
     */
    private function applyTextFilter(
        Builder $query,
        string $column,
        string $value,
        bool $ignoreCase,
    ): void {
        $operator = $ignoreCase ? 'ilike' : 'like';

        $query->where(
            $column,
            $operator,
            '%' . $value . '%'
        );
    }
}