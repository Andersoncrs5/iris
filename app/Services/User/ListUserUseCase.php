<?php

namespace App\Services\User;

use App\DTOs\User\FilterUserDTO;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListUserUseCase
{
    /**
     * List users according to the provided filters.
     *
     * @param FilterUserDTO $dto Filters, sorting, and pagination options.
     *
     * @return LengthAwarePaginator<User>
     */
    public function execute(
        FilterUserDTO $dto
    ): LengthAwarePaginator {
        $query = User::query();

        // Soft Deletes handling
        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        // User ID filter
        if ($dto->id !== null) {
            $query->where('id', $dto->id);
        }

        // Name filter
        if ($dto->name !== null) {
            $this->applyTextFilter(
                $query,
                'name',
                $dto->name,
                $dto->nameIgnoreCase
            );
        }

        // Full name filter
        if ($dto->fullName !== null) {
            $this->applyTextFilter(
                $query,
                'full_name',
                $dto->fullName,
                $dto->fullNameIgnoreCase
            );
        }

        // Email filter
        if ($dto->email !== null) {
            $this->applyTextFilter(
                $query,
                'email',
                $dto->email,
                $dto->emailIgnoreCase
            );
        }

        // Account status filter
        if ($dto->status !== null) {
            $query->where('status', $dto->status);
        }

        // Email verification filter
        if ($dto->emailVerified === true) {
            $query->whereNotNull('email_verified_at');
        } elseif ($dto->emailVerified === false) {
            $query->whereNull('email_verified_at');
        }

        // Email verification date range
        $this->applyDateRange(
            $query,
            'email_verified_at',
            $dto->emailVerifiedAtFrom,
            $dto->emailVerifiedAtTo
        );

        // Last login date range
        $this->applyDateRange(
            $query,
            'last_login_at',
            $dto->lastLoginAtFrom,
            $dto->lastLoginAtTo
        );

        // Account creation date range
        $this->applyDateRange(
            $query,
            'created_at',
            $dto->createdAtFrom,
            $dto->createdAtTo
        );

        // Account update date range
        $this->applyDateRange(
            $query,
            'updated_at',
            $dto->updatedAtFrom,
            $dto->updatedAtTo
        );

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
     * The column must be defined internally by the application.
     *
     * @param Builder<User> $query
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
     * Apply an inclusive date or datetime range.
     *
     * Date-only lower bounds start at midnight. Date-only upper
     * bounds end at the last microsecond of the specified day.
     * Datetime values retain their specified time.
     *
     * @param Builder<User> $query
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
     * Normalize a date boundary for SQL filtering.
     *
     * @param string $value Date or datetime value.
     * @param bool $endOfDay Whether to use the end of a date-only value.
     *
     * @return Carbon
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
