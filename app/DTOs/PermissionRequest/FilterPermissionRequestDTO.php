<?php

namespace App\DTOs\PermissionRequest;

use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;
use Illuminate\Http\Request;

final readonly class FilterPermissionRequestDTO
{
    private const SORTABLE_COLUMNS = [
        'id',
        'user_id',
        'permission_id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'viewed_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @param list<string> $statuses
     */
    public function __construct(
        public ?int $userId = null,

        public ?string $userName = null,
        public bool $userNameIgnoreCase = true,

        public ?string $userEmail = null,
        public bool $userEmailIgnoreCase = true,

        public ?int $permissionId = null,

        public ?string $permissionName = null,
        public bool $permissionNameIgnoreCase = true,

        public ?string $permissionSlug = null,
        public bool $permissionSlugIgnoreCase = true,

        public array $statuses = [],

        public ?string $reason = null,
        public bool $reasonIgnoreCase = true,

        public ?int $reviewedBy = null,

        public ?string $reviewComment = null,
        public bool $reviewCommentIgnoreCase = true,

        public ?bool $viewed = null,

        public ?string $createdAtFrom = null,
        public ?string $createdAtTo = null,

        public ?string $reviewedAtFrom = null,
        public ?string $reviewedAtTo = null,

        public int $perPage = 30,
        public string $sortBy = 'id',
        public string $sortOrder = 'desc',
    ) {}

    /**
     * Create the DTO from request query parameters.
     */
    public static function fromRequest(Request $request): self
    {
        $sortByInput = self::nullableString(
            $request->input('sort_by')
        );

        $sortOrderInput = strtolower(
            trim((string) $request->input('sort_order', 'desc'))
        );

        $perPage = filter_var(
            $request->input('per_page', 30),
            FILTER_VALIDATE_INT
        );

        if ($perPage === false) {
            $perPage = 30;
        }

        return new self(
            userId: self::nullablePositiveInteger(
                $request->input('user_id')
            ),

            userName: self::nullableString(
                $request->input('user_name')
            ),

            userNameIgnoreCase: self::booleanOrDefault(
                $request->input('user_name_ignore_case'),
                true
            ),

            userEmail: self::nullableString(
                $request->input('user_email')
            ),

            userEmailIgnoreCase: self::booleanOrDefault(
                $request->input('user_email_ignore_case'),
                true
            ),

            permissionId: self::nullablePositiveInteger(
                $request->input('permission_id')
            ),

            permissionName: self::nullableString(
                $request->input('permission_name')
            ),

            permissionNameIgnoreCase: self::booleanOrDefault(
                $request->input('permission_name_ignore_case'),
                true
            ),

            permissionSlug: self::nullableString(
                $request->input('permission_slug')
            ),

            permissionSlugIgnoreCase: self::booleanOrDefault(
                $request->input('permission_slug_ignore_case'),
                true
            ),

            statuses: self::normalizeStatuses(
                $request->input('status')
            ),

            reason: self::nullableString(
                $request->input('reason')
            ),

            reasonIgnoreCase: self::booleanOrDefault(
                $request->input('reason_ignore_case'),
                true
            ),

            reviewedBy: self::nullablePositiveInteger(
                $request->input('reviewed_by')
            ),

            reviewComment: self::nullableString(
                $request->input('review_comment')
            ),

            reviewCommentIgnoreCase: self::booleanOrDefault(
                $request->input('review_comment_ignore_case'),
                true
            ),

            viewed: self::nullableBoolean(
                $request->input('viewed')
            ),

            createdAtFrom: self::nullableDate(
                $request->input('created_at_from')
            ),

            createdAtTo: self::nullableDate(
                $request->input('created_at_to')
            ),

            reviewedAtFrom: self::nullableDate(
                $request->input('reviewed_at_from')
            ),

            reviewedAtTo: self::nullableDate(
                $request->input('reviewed_at_to')
            ),

            perPage: max(1, min($perPage, 100)),

            sortBy: in_array(
                $sortByInput,
                self::SORTABLE_COLUMNS,
                true
            )
                ? $sortByInput
                : 'id',

            sortOrder: in_array(
                $sortOrderInput,
                ['asc', 'desc'],
                true
            )
                ? $sortOrderInput
                : 'desc',
        );
    }

    /**
     * Normalize one or multiple status values.
     *
     * @return list<string>
     */
    private static function normalizeStatuses(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $values = is_array($value) ? $value : [$value];
        $validStatuses = PermissionRequestStatus::values();
        $statuses = [];

        foreach ($values as $item) {
            $status = self::nullableString($item);

            if (
                $status !== null
                && in_array($status, $validStatuses, true)
            ) {
                $statuses[] = $status;
            }
        }

        return array_values(array_unique($statuses));
    }

    private static function nullableString(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private static function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $integer = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

        return $integer === false ? null : $integer;
    }

    private static function nullablePositiveInteger(
        mixed $value
    ): ?int {
        $integer = self::nullableInteger($value);

        return $integer !== null && $integer > 0
            ? $integer
            : null;
    }

    private static function nullableBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );
    }

    private static function booleanOrDefault(
        mixed $value,
        bool $default
    ): bool {
        if ($value === null || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        $boolean = filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        );

        return $boolean ?? $default;
    }

    /**
     * Normalize a date in YYYY-MM-DD format.
     */
    private static function nullableDate(mixed $value): ?string
    {
        $value = self::nullableString($value);

        if ($value === null) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $value
        );

        $errors = \DateTimeImmutable::getLastErrors();

        if (
            $date === false
            || (
                $errors !== false
                && (
                    $errors['warning_count'] > 0
                    || $errors['error_count'] > 0
                )
            )
            || $date->format('Y-m-d') !== $value
        ) {
            return null;
        }

        return $value;
    }
}