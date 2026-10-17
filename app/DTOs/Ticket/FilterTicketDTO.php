<?php

namespace App\DTOs\Ticket;

use App\Utils\Enums\Ticket\TicketColumnEnum;
use Illuminate\Http\Request;

final readonly class FilterTicketDTO
{
    private const ALLOWED_SOURCES = [
        'WEB',
        'EMAIL',
        'API',
        'CHAT',
        'WHATSAPP',
        'PHONE',
    ];

    public function __construct(
        public ?int $ticketNumber = null,

        public ?int $userId = null,
        public ?string $userName = null,
        public bool $userNameIgnoreCase = true,
        public ?string $userEmail = null,
        public bool $userEmailIgnoreCase = true,

        public ?int $agentId = null,
        public ?string $agentName = null,
        public bool $agentNameIgnoreCase = true,
        public ?string $agentEmail = null,
        public bool $agentEmailIgnoreCase = true,

        public ?int $teamId = null,

        public ?int $categoryId = null,
        public ?string $categoryName = null,
        public bool $categoryNameIgnoreCase = true,
        public ?string $categorySlug = null,
        public bool $categorySlugIgnoreCase = true,

        public ?int $statusId = null,
        public ?string $statusName = null,
        public bool $statusNameIgnoreCase = true,

        public ?int $priorityId = null,
        public ?string $priorityName = null,
        public bool $priorityNameIgnoreCase = true,
        public ?string $codeName = null,
        public bool $codeNameIgnoreCase = true,

        public ?string $subject = null,
        public bool $subjectIgnoreCase = true,

        public ?string $description = null,
        public bool $descriptionIgnoreCase = true,

        /** @var list<string> */
        public array $source = [],

        public ?int $satisfactionRatingMin = null,
        public ?int $satisfactionRatingMax = null,

        public ?string $satisfactionComment = null,
        public bool $satisfactionCommentIgnoreCase = true,

        public ?bool $isEscalated = null,

        public ?string $openedAtFrom = null,
        public ?string $openedAtTo = null,

        public ?string $lastActivityAtFrom = null,
        public ?string $lastActivityAtTo = null,

        public ?string $firstResponseAtFrom = null,
        public ?string $firstResponseAtTo = null,

        public ?string $resolvedAtFrom = null,
        public ?string $resolvedAtTo = null,

        public ?string $closedAtFrom = null,
        public ?string $closedAtTo = null,

        public ?string $dueAtFrom = null,
        public ?string $dueAtTo = null,

        public ?string $createdAtFrom = null,
        public ?string $createdAtTo = null,

        public ?string $updatedAtFrom = null,
        public ?string $updatedAtTo = null,

        public ?string $trashed = null,

        public int $perPage = 15,
        public TicketColumnEnum $sortBy = TicketColumnEnum::ID,
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
            $request->input('per_page', 15),
            FILTER_VALIDATE_INT
        );

        if ($perPage === false) {
            $perPage = 15;
        }

        $trashedInput = self::nullableString(
            $request->input('trashed')
        );

        $trashed = in_array(
            $trashedInput,
            ['with', 'only'],
            true
        )
            ? $trashedInput
            : null;

        return new self(
            // Ticket identity
            ticketNumber: self::nullablePositiveInteger(
                $request->input('ticket_number')
            ),

            // Ticket owner
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

            // Assigned agent
            agentId: self::nullablePositiveInteger(
                $request->input('agent_id')
            ),

            agentName: self::nullableString(
                $request->input('agent_name')
            ),

            agentNameIgnoreCase: self::booleanOrDefault(
                $request->input('agent_name_ignore_case'),
                true
            ),

            agentEmail: self::nullableString(
                $request->input('agent_email')
            ),

            agentEmailIgnoreCase: self::booleanOrDefault(
                $request->input('agent_email_ignore_case'),
                true
            ),

            // Team
            teamId: self::nullablePositiveInteger(
                $request->input('team_id')
            ),

            // Category
            categoryId: self::nullablePositiveInteger(
                $request->input('category_id')
            ),

            categoryName: self::nullableString(
                $request->input('category_name')
            ),

            categoryNameIgnoreCase: self::booleanOrDefault(
                $request->input('category_name_ignore_case'),
                true
            ),

            categorySlug: self::nullableString(
                $request->input('category_slug')
            ),

            categorySlugIgnoreCase: self::booleanOrDefault(
                $request->input('category_slug_ignore_case'),
                true
            ),

            // Status
            statusId: self::nullablePositiveInteger(
                $request->input('status_id')
            ),

            statusName: self::nullableString(
                $request->input('status_name')
            ),

            statusNameIgnoreCase: self::booleanOrDefault(
                $request->input('status_name_ignore_case'),
                true
            ),

            // Priority
            priorityId: self::nullablePositiveInteger(
                $request->input('priority_id')
            ),

            priorityName: self::nullableString(
                $request->input('priority_name')
            ),

            priorityNameIgnoreCase: self::booleanOrDefault(
                $request->input('priority_name_ignore_case'),
                true
            ),

            codeName: self::nullableString(
                $request->input('code_name')
            ),

            codeNameIgnoreCase: self::booleanOrDefault(
                $request->input('code_name_ignore_case'),
                true
            ),

            // Subject
            subject: self::nullableString(
                $request->input('subject')
            ),

            subjectIgnoreCase: self::booleanOrDefault(
                $request->input('subject_ignore_case'),
                true
            ),

            // Description
            description: self::nullableString(
                $request->input('description')
            ),

            descriptionIgnoreCase: self::booleanOrDefault(
                $request->input('description_ignore_case'),
                true
            ),

            // Ticket sources: supports source[]=WEB&source[]=API
            source: self::normalizeSources(
                $request->input('source')
            ),

            // Satisfaction rating
            satisfactionRatingMin: self::nullableInteger(
                $request->input('satisfaction_rating_min')
            ),

            satisfactionRatingMax: self::nullableInteger(
                $request->input('satisfaction_rating_max')
            ),

            satisfactionComment: self::nullableString(
                $request->input('satisfaction_comment')
            ),

            satisfactionCommentIgnoreCase: self::booleanOrDefault(
                $request->input('satisfaction_comment_ignore_case'),
                true
            ),

            // Escalation
            isEscalated: self::nullableBoolean(
                $request->input('is_escalated')
            ),

            // Date ranges
            openedAtFrom: self::nullableDate(
                $request->input('opened_at_from')
            ),

            openedAtTo: self::nullableDate(
                $request->input('opened_at_to')
            ),

            lastActivityAtFrom: self::nullableDate(
                $request->input('last_activity_at_from')
            ),

            lastActivityAtTo: self::nullableDate(
                $request->input('last_activity_at_to')
            ),

            firstResponseAtFrom: self::nullableDate(
                $request->input('first_response_at_from')
            ),

            firstResponseAtTo: self::nullableDate(
                $request->input('first_response_at_to')
            ),

            resolvedAtFrom: self::nullableDate(
                $request->input('resolved_at_from')
            ),

            resolvedAtTo: self::nullableDate(
                $request->input('resolved_at_to')
            ),

            closedAtFrom: self::nullableDate(
                $request->input('closed_at_from')
            ),

            closedAtTo: self::nullableDate(
                $request->input('closed_at_to')
            ),

            dueAtFrom: self::nullableDate(
                $request->input('due_at_from')
            ),

            dueAtTo: self::nullableDate(
                $request->input('due_at_to')
            ),

            createdAtFrom: self::nullableDate(
                $request->input('created_at_from')
            ),

            createdAtTo: self::nullableDate(
                $request->input('created_at_to')
            ),

            updatedAtFrom: self::nullableDate(
                $request->input('updated_at_from')
            ),

            updatedAtTo: self::nullableDate(
                $request->input('updated_at_to')
            ),

            // Soft deletes
            trashed: $trashed,

            // Pagination
            perPage: max(1, min($perPage, 100)),

            // Sorting
            sortBy: TicketColumnEnum::safeTryFrom($sortByInput)
                ?? TicketColumnEnum::ID,

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
     * Normalize a value into a nullable string.
     */
    private static function nullableString(mixed $value): ?string
    {
        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Normalize an integer.
     */
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

    /**
     * Normalize a positive integer, suitable for IDs.
     */
    private static function nullablePositiveInteger(
        mixed $value
    ): ?int {
        $integer = self::nullableInteger($value);

        return $integer !== null && $integer > 0
            ? $integer
            : null;
    }

    /**
     * Normalize selected source values.
     *
     * @return list<string>
     */
    private static function normalizeSources(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $values = is_array($value) ? $value : [$value];
        $sources = [];

        foreach ($values as $item) {
            $source = self::nullableString($item);

            if ($source === null) {
                continue;
            }

            $source = strtoupper($source);

            if (in_array($source, self::ALLOWED_SOURCES, true)) {
                $sources[] = $source;
            }
        }

        return array_values(array_unique($sources));
    }

    /**
     * Normalize a nullable boolean.
     */
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

    /**
     * Normalize a boolean with a default value.
     */
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
     * Normalize a date or datetime.
     *
     * Supported formats:
     * - YYYY-MM-DD
     * - YYYY-MM-DD HH:MM
     * - YYYY-MM-DD HH:MM:SS
     * - The same datetime formats using "T" as separator.
     *
     * Invalid values are ignored.
     */
    private static function nullableDate(mixed $value): ?string
    {
        $value = self::nullableString($value);

        if ($value === null) {
            return null;
        }

        $value = str_replace('T', ' ', $value);

        $formats = [
            ['!Y-m-d', 'Y-m-d', 'Y-m-d'],
            ['!Y-m-d H:i', 'Y-m-d H:i', 'Y-m-d H:i:s'],
            ['!Y-m-d H:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i:s'],
        ];

        foreach ($formats as [$inputFormat, $expectedFormat, $outputFormat]) {
            $date = \DateTimeImmutable::createFromFormat(
                $inputFormat,
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
                || $date->format($expectedFormat) !== $value
            ) {
                continue;
            }

            return $date->format($outputFormat);
        }

        return null;
    }
}