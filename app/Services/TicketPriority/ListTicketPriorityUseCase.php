<?php

namespace App\Services\TicketPriority;

use App\DTOs\TicketPriority\FilterTicketPriorityDTO;
use App\Models\TicketPriorityModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListTicketPriorityUseCase
{
    public function execute(
        FilterTicketPriorityDTO $dto
    ): LengthAwarePaginator {
        $query = TicketPriorityModel::query();

        // Soft Deletes handling
        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        // Name filter
        if ($dto->name !== null && $dto->name !== '') {
            $this->applyTextFilter(
                $query,
                'name',
                $dto->name,
                $dto->nameIgnoreCase
            );
        }

        // Code filter
        if ($dto->code !== null && $dto->code !== '') {
            $this->applyTextFilter(
                $query,
                'code',
                $dto->code,
                $dto->codeIgnoreCase
            );
        }

        // Level filter
        if ($dto->level !== null) {
            $query->where('level', $dto->level);
        }

        // Description filter
        if ($dto->description !== null && $dto->description !== '') {
            $this->applyTextFilter(
                $query,
                'description',
                $dto->description,
                $dto->descriptionIgnoreCase
            );
        }

        // Color filter
        if ($dto->color !== null && $dto->color !== '') {
            $this->applyTextFilter(
                $query,
                'color',
                $dto->color,
                $dto->colorIgnoreCase
            );
        }

        // Icon filter
        if ($dto->icon !== null && $dto->icon !== '') {
            $this->applyTextFilter(
                $query,
                'icon',
                $dto->icon,
                $dto->iconIgnoreCase
            );
        }

        // Response time range
        $this->applyRangeFilter(
            $query,
            'response_time_minutes',
            $dto->responseTimeMinutesMin,
            $dto->responseTimeMinutesMax
        );

        // Resolution time range
        $this->applyRangeFilter(
            $query,
            'resolution_time_minutes',
            $dto->resolutionTimeMinutesMin,
            $dto->resolutionTimeMinutesMax
        );

        // Escalation time range
        $this->applyRangeFilter(
            $query,
            'escalation_time_minutes',
            $dto->escalationTimeMinutesMin,
            $dto->escalationTimeMinutesMax
        );

        // Default priority filter
        if ($dto->isDefault !== null) {
            $query->where('is_default', $dto->isDefault);
        }

        // Active priority filter
        if ($dto->isActive !== null) {
            $query->where('is_active', $dto->isActive);
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
     * Apply a case-sensitive or case-insensitive text filter.
     *
     * PostgreSQL supports LIKE and ILIKE operators.
     *
     * @param Builder<TicketPriorityModel> $query
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
     * Apply optional minimum and maximum bounds to a numeric column.
     *
     * The bounds are inclusive. Null bounds are ignored.
     *
     * @param Builder<TicketPriorityModel> $query
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