<?php

namespace App\Services\Ticket;

use App\DTOs\Ticket\FilterTicketDTO;
use App\Models\TicketModel;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListTicketUseCase
{
    public function execute(
        FilterTicketDTO $dto
    ): LengthAwarePaginator {
        $query = TicketModel::query()
            ->with([
                'user',
                'agent',
                'category',
                'status',
                'priority',
            ]);

        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        if ($dto->ticketNumber !== null) {
            $query->where('ticket_number', $dto->ticketNumber);
        }

        if ($dto->userId !== null) {
            $query->where('user_id', $dto->userId);
        }

        if ($dto->agentId !== null) {
            $query->where('agent_id', $dto->agentId);
        }

        if ($dto->teamId !== null) {
            $query->where('team_id', $dto->teamId);
        }

        if ($dto->categoryId !== null) {
            $query->where('category_id', $dto->categoryId);
        }

        if ($dto->statusId !== null) {
            $query->where('status_id', $dto->statusId);
        }

        if ($dto->priorityId !== null) {
            $query->where('priority_id', $dto->priorityId);
        }

        if ($dto->subject !== null) {
            $this->applyTextFilter(
                $query,
                'subject',
                $dto->subject,
                $dto->subjectIgnoreCase
            );
        }

        if ($dto->description !== null) {
            $this->applyTextFilter(
                $query,
                'description',
                $dto->description,
                $dto->descriptionIgnoreCase
            );
        }

        if ($dto->source !== null) {
            $query->where('source', $dto->source);
        }

        $this->applyRangeFilter(
            $query,
            'satisfaction_rating',
            $dto->satisfactionRatingMin,
            $dto->satisfactionRatingMax
        );

        if ($dto->satisfactionComment !== null) {
            $this->applyTextFilter(
                $query,
                'satisfaction_comment',
                $dto->satisfactionComment,
                $dto->satisfactionCommentIgnoreCase
            );
        }

        if ($dto->isEscalated !== null) {
            $query->where('is_escalated', $dto->isEscalated);
        }

        $this->applyDateRange(
            $query,
            'opened_at',
            $dto->openedAtFrom,
            $dto->openedAtTo
        );

        $this->applyDateRange(
            $query,
            'last_activity_at',
            $dto->lastActivityAtFrom,
            $dto->lastActivityAtTo
        );

        $this->applyDateRange(
            $query,
            'first_response_at',
            $dto->firstResponseAtFrom,
            $dto->firstResponseAtTo
        );

        $this->applyDateRange(
            $query,
            'resolved_at',
            $dto->resolvedAtFrom,
            $dto->resolvedAtTo
        );

        $this->applyDateRange(
            $query,
            'closed_at',
            $dto->closedAtFrom,
            $dto->closedAtTo
        );

        $this->applyDateRange(
            $query,
            'due_at',
            $dto->dueAtFrom,
            $dto->dueAtTo
        );

        $this->applyDateRange(
            $query,
            'created_at',
            $dto->createdAtFrom,
            $dto->createdAtTo
        );

        $this->applyDateRange(
            $query,
            'updated_at',
            $dto->updatedAtFrom,
            $dto->updatedAtTo
        );

        $query->orderBy(
            $dto->sortBy->value,
            $dto->sortOrder
        );

        return $query
            ->paginate($dto->perPage)
            ->withQueryString();
    }

    /**
     * Apply a case-sensitive or case-insensitive text filter.
     *
     * PostgreSQL supports LIKE and ILIKE operators.
     *
     * @param Builder<TicketModel> $query
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
     * Both bounds are inclusive. Null bounds are ignored.
     *
     * @param Builder<TicketModel> $query
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

    /**
     * Apply an inclusive date or datetime range to a timestamp column.
     *
     * Date-only lower bounds start at midnight, while date-only upper
     * bounds end at the last microsecond of the requested day.
     * Datetime inputs retain their specified time.
     *
     * @param Builder<TicketModel> $query
     */
    private function applyDateRange(
        Builder $query,
        string $column,
        ?string $from,
        ?string $to
    ): void {
        if ($from !== null) {
            $query->where(
                $column,
                '>=',
                $this->normalizeDateBoundary($from, false)
            );
        }

        if ($to !== null) {
            $query->where(
                $column,
                '<=',
                $this->normalizeDateBoundary($to, true)
            );
        }
    }

    /**
     * Normalize a date-only value to the beginning or end of its day.
     *
     * Datetime values retain their supplied time.
     */
    private function normalizeDateBoundary(
        string $value,
        bool $endOfDay
    ): Carbon {
        $date = Carbon::parse($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $endOfDay
                ? $date->endOfDay()
                : $date->startOfDay();
        }

        return $date;
    }
}
