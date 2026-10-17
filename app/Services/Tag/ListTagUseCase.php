<?php

namespace App\Services\Tag;

use App\DTOs\Tag\FilterTagDTO;
use App\Models\TagModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListTagUseCase
{
    public function execute(
        FilterTagDTO $dto
    ): LengthAwarePaginator {
        $query = TagModel::query();

        // Soft deletes
        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        // Name
        if ($dto->name !== null) {
            $this->applyTextFilter(
                $query,
                'name',
                $dto->name,
                $dto->nameIgnoreCase
            );
        }

        // Slug
        if ($dto->slug !== null) {
            $this->applyTextFilter(
                $query,
                'slug',
                $dto->slug,
                $dto->slugIgnoreCase
            );
        }

        // Color
        if ($dto->color !== null) {
            $this->applyTextFilter(
                $query,
                'color',
                $dto->color,
                $dto->colorIgnoreCase
            );
        }

        // One or multiple tag types
        if ($dto->type !== null && $dto->type !== []) {
            $query->whereIn('type', $dto->type);
        }

        // Description
        if ($dto->description !== null) {
            $this->applyTextFilter(
                $query,
                'description',
                $dto->description,
                $dto->descriptionIgnoreCase
            );
        }

        // Usage count range
        $this->applyRangeFilter(
            $query,
            'usage_count',
            $dto->usageCountMin,
            $dto->usageCountMax
        );

        // Active state
        if ($dto->isActive !== null) {
            $query->where(
                'is_active',
                $dto->isActive
            );
        }

        // Sorting
        $query->orderBy(
            $dto->sortBy->value,
            $dto->sortOrder
        );

        // Pagination
        return $query
            ->paginate($dto->perPage)
            ->withQueryString();
    }

    /**
     * Apply a case-sensitive or case-insensitive partial text filter.
     *
     * PostgreSQL supports LIKE and ILIKE operators.
     *
     * @param Builder<TagModel> $query
     */
    private function applyTextFilter(
        Builder $query,
        string $column,
        string $value,
        bool $ignoreCase
    ): void {
        $operator = $ignoreCase ? 'ilike' : 'like';

        $query->where(
            $column,
            $operator,
            '%' . $value . '%'
        );
    }

    /**
     * Apply inclusive numeric bounds.
     *
     * @param Builder<TagModel> $query
     */
    private function applyRangeFilter(
        Builder $query,
        string $column,
        ?int $min,
        ?int $max
    ): void {
        if ($min !== null) {
            $query->where($column, '>=', $min);
        }

        if ($max !== null) {
            $query->where($column, '<=', $max);
        }
    }
}