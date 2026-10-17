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
        FilterTicketDTO $dto,
        ?int $id = null
    ): LengthAwarePaginator {
        $query = TicketModel::query()
            ->with([
                'user',
                'agent',
                'category',
                'status',
                'priority',
            ]);

        // Soft deletes
        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        // Ticket number
        if ($dto->ticketNumber !== null) {
            $query->where(
                'ticket_number',
                $dto->ticketNumber
            );
        }

        // User ID
        if ($dto->userId !== null) {
            $query->where('user_id', $dto->userId);
        }

        // User name
        if ($dto->userName !== null) {
            $query->whereHas(
                'user',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'name',
                    $dto->userName,
                    $dto->userNameIgnoreCase
                )
            );
        }

        // User email
        if ($dto->userEmail !== null) {
            $query->whereHas(
                'user',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'email',
                    $dto->userEmail,
                    $dto->userEmailIgnoreCase
                )
            );
        }

        // Agent ID
        if ($dto->agentId !== null) {
            $query->where('agent_id', $dto->agentId);
        }

        // Agent name
        if ($dto->agentName !== null) {
            $query->whereHas(
                'agent',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'name',
                    $dto->agentName,
                    $dto->agentNameIgnoreCase
                )
            );
        }

        // Agent email
        if ($dto->agentEmail !== null) {
            $query->whereHas(
                'agent',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'email',
                    $dto->agentEmail,
                    $dto->agentEmailIgnoreCase
                )
            );
        }

        // Team
        if ($dto->teamId !== null) {
            $query->where('team_id', $dto->teamId);
        }

        // Category ID
        if ($dto->categoryId !== null) {
            $query->where('category_id', $dto->categoryId);
        }

        // Category name
        if ($dto->categoryName !== null) {
            $query->whereHas(
                'category',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'name',
                    $dto->categoryName,
                    $dto->categoryNameIgnoreCase
                )
            );
        }

        // Category slug
        if ($dto->categorySlug !== null) {
            $query->whereHas(
                'category',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'slug',
                    $dto->categorySlug,
                    $dto->categorySlugIgnoreCase
                )
            );
        }

        // Status ID
        if ($dto->statusId !== null) {
            $query->where('status_id', $dto->statusId);
        }

        // Status name
        if ($dto->statusName !== null) {
            $query->whereHas(
                'status',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'name',
                    $dto->statusName,
                    $dto->statusNameIgnoreCase
                )
            );
        }

        // Priority ID
        if ($dto->priorityId !== null) {
            $query->where('priority_id', $dto->priorityId);
        }

        // Priority name
        if ($dto->priorityName !== null) {
            $query->whereHas(
                'priority',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'name',
                    $dto->priorityName,
                    $dto->priorityNameIgnoreCase
                )
            );
        }

        // Priority code
        if ($dto->codeName !== null) {
            $query->whereHas(
                'priority',
                fn (Builder $relatedQuery) => $this->applyTextFilter(
                    $relatedQuery,
                    'code',
                    $dto->codeName,
                    $dto->codeNameIgnoreCase
                )
            );
        }

        // Subject
        if ($dto->subject !== null) {
            $this->applyTextFilter(
                $query,
                'subject',
                $dto->subject,
                $dto->subjectIgnoreCase
            );
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

        // Source: multiple values
        if ($dto->source !== []) {
            $query->whereIn('source', $dto->source);
        }

        // Satisfaction rating range
        $this->applyRangeFilter(
            $query,
            'satisfaction_rating',
            $dto->satisfactionRatingMin,
            $dto->satisfactionRatingMax
        );

        // Satisfaction comment
        if ($dto->satisfactionComment !== null) {
            $this->applyTextFilter(
                $query,
                'satisfaction_comment',
                $dto->satisfactionComment,
                $dto->satisfactionCommentIgnoreCase
            );
        }

        // Escalation state
        if ($dto->isEscalated !== null) {
            $query->where(
                'is_escalated',
                $dto->isEscalated
            );
        }

        // Opened date range
        $this->applyDateRange(
            $query,
            'opened_at',
            $dto->openedAtFrom,
            $dto->openedAtTo
        );

        // Last activity date range
        $this->applyDateRange(
            $query,
            'last_activity_at',
            $dto->lastActivityAtFrom,
            $dto->lastActivityAtTo
        );

        // First response date range
        $this->applyDateRange(
            $query,
            'first_response_at',
            $dto->firstResponseAtFrom,
            $dto->firstResponseAtTo
        );

        // Resolution date range
        $this->applyDateRange(
            $query,
            'resolved_at',
            $dto->resolvedAtFrom,
            $dto->resolvedAtTo
        );

        // Closing date range
        $this->applyDateRange(
            $query,
            'closed_at',
            $dto->closedAtFrom,
            $dto->closedAtTo
        );

        // Due date range
        $this->applyDateRange(
            $query,
            'due_at',
            $dto->dueAtFrom,
            $dto->dueAtTo
        );

        // Creation date range
        $this->applyDateRange(
            $query,
            'created_at',
            $dto->createdAtFrom,
            $dto->createdAtTo
        );

        // Update date range
        $this->applyDateRange(
            $query,
            'updated_at',
            $dto->updatedAtFrom,
            $dto->updatedAtTo
        );

        // Optional user scope, for example, the current user's tickets
        if ($id !== null) {
            $query->where('user_id', $id);
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
     * Apply case-sensitive or case-insensitive partial text matching.
     *
     * PostgreSQL supports LIKE and ILIKE.
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
     * Apply inclusive minimum and maximum numeric bounds.
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
     * Apply an inclusive date or datetime range.
     *
     * Date-only inputs include the entire requested day.
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