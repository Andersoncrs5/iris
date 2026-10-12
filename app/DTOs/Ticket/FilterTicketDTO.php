<?php

namespace App\DTOs\Ticket;

use App\Utils\Enums\Ticket\TicketColumnEnum;
use Illuminate\Http\Request;

final readonly class FilterTicketDTO
{
    /**
     * @param int|null $ticketNumber Public ticket number.
     * @param int|null $userId Ticket owner's ID.
     * @param int|null $agentId Assigned agent's ID.
     * @param int|null $teamId Assigned team's ID.
     * @param int|null $categoryId Category ID.
     * @param int|null $statusId Status ID.
     * @param int|null $priorityId Priority ID.
     * @param string|null $subject Subject filter.
     * @param bool $subjectIgnoreCase Whether subject matching ignores case.
     * @param string|null $description Description filter.
     * @param bool $descriptionIgnoreCase Whether description matching ignores case.
     * @param string|null $source Ticket origin.
     * @param int|null $satisfactionRatingMin Minimum satisfaction rating.
     * @param int|null $satisfactionRatingMax Maximum satisfaction rating.
     * @param string|null $satisfactionComment Satisfaction comment filter.
     * @param bool $satisfactionCommentIgnoreCase Whether comment matching ignores case.
     * @param bool|null $isEscalated Escalation state filter.
     * @param string|null $openedAtFrom Start of the opening date range.
     * @param string|null $openedAtTo End of the opening date range.
     * @param string|null $lastActivityAtFrom Start of the last activity date range.
     * @param string|null $lastActivityAtTo End of the last activity date range.
     * @param string|null $firstResponseAtFrom Start of the first response date range.
     * @param string|null $firstResponseAtTo End of the first response date range.
     * @param string|null $resolvedAtFrom Start of the resolution date range.
     * @param string|null $resolvedAtTo End of the resolution date range.
     * @param string|null $closedAtFrom Start of the closing date range.
     * @param string|null $closedAtTo End of the closing date range.
     * @param string|null $dueAtFrom Start of the due date range.
     * @param string|null $dueAtTo End of the due date range.
     * @param string|null $createdAtFrom Start of the creation date range.
     * @param string|null $createdAtTo End of the creation date range.
     * @param string|null $updatedAtFrom Start of the update date range.
     * @param string|null $updatedAtTo End of the update date range.
     * @param string|null $trashed Soft delete filter: with or only.
     * @param int $perPage Number of records per page.
     * @param TicketColumnEnum $sortBy Allowed sorting column.
     * @param string $sortOrder Sorting direction: asc or desc.
     */
    public function __construct(
        public ?int $ticketNumber = null,

        public ?int $userId = null,
        public ?int $agentId = null,
        public ?int $teamId = null,
        public ?int $categoryId = null,
        public ?int $statusId = null,
        public ?int $priorityId = null,

        public ?string $subject = null,
        public bool $subjectIgnoreCase = true,

        public ?string $description = null,
        public bool $descriptionIgnoreCase = true,

        public ?string $source = null,

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
     *
     * @param Request $request HTTP request.
     *
     * @return self
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

        $trashed = in_array($trashedInput, ['with', 'only'], true)
            ? $trashedInput
            : null;

        $sourceInput = self::nullableString(
            $request->input('source')
        );

        $sourceInput = $sourceInput !== null
            ? strtoupper($sourceInput)
            : null;

        $allowedSources = [
            'WEB',
            'EMAIL',
            'API',
            'CHAT',
            'WHATSAPP',
            'PHONE',
        ];

        $source = in_array($sourceInput, $allowedSources, true)
            ? $sourceInput
            : null;

        return new self(
            // Ticket identity
            ticketNumber: self::nullableInteger(
                $request->input('ticket_number')
            ),

            // Related entities
            userId: self::nullableInteger($request->input('user_id')),
            agentId: self::nullableInteger($request->input('agent_id')),
            teamId: self::nullableInteger($request->input('team_id')),
            categoryId: self::nullableInteger($request->input('category_id')),
            statusId: self::nullableInteger($request->input('status_id')),
            priorityId: self::nullableInteger($request->input('priority_id')),

            // Subject
            subject: self::nullableString($request->input('subject')),
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

            // Ticket source
            source: $source,

            // Satisfaction rating range
            satisfactionRatingMin: self::nullableInteger(
                $request->input('satisfaction_rating_min')
            ),
            satisfactionRatingMax: self::nullableInteger(
                $request->input('satisfaction_rating_max')
            ),

            // Satisfaction comment
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

            // Opened date range
            openedAtFrom: self::nullableString(
                $request->input('opened_at_from')
            ),
            openedAtTo: self::nullableString(
                $request->input('opened_at_to')
            ),

            // Last activity date range
            lastActivityAtFrom: self::nullableString(
                $request->input('last_activity_at_from')
            ),
            lastActivityAtTo: self::nullableString(
                $request->input('last_activity_at_to')
            ),

            // First response date range
            firstResponseAtFrom: self::nullableString(
                $request->input('first_response_at_from')
            ),
            firstResponseAtTo: self::nullableString(
                $request->input('first_response_at_to')
            ),

            // Resolution date range
            resolvedAtFrom: self::nullableString(
                $request->input('resolved_at_from')
            ),
            resolvedAtTo: self::nullableString(
                $request->input('resolved_at_to')
            ),

            // Closing date range
            closedAtFrom: self::nullableString(
                $request->input('closed_at_from')
            ),
            closedAtTo: self::nullableString(
                $request->input('closed_at_to')
            ),

            // Due date range
            dueAtFrom: self::nullableString(
                $request->input('due_at_from')
            ),
            dueAtTo: self::nullableString(
                $request->input('due_at_to')
            ),

            // Creation date range
            createdAtFrom: self::nullableString(
                $request->input('created_at_from')
            ),
            createdAtTo: self::nullableString(
                $request->input('created_at_to')
            ),

            // Update date range
            updatedAtFrom: self::nullableString(
                $request->input('updated_at_from')
            ),
            updatedAtTo: self::nullableString(
                $request->input('updated_at_to')
            ),

            // Soft Deletes
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
     *
     * @param mixed $value Value to normalize.
     *
     * @return string|null
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
     * Normalize a value into a nullable integer.
     *
     * @param mixed $value Value to normalize.
     *
     * @return int|null
     */
    private static function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer === false ? null : $integer;
    }

    /**
     * Normalize a value into a nullable boolean.
     *
     * @param mixed $value Value to normalize.
     *
     * @return bool|null
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
     * Normalize a boolean value, falling back to the provided default.
     *
     * @param mixed $value Value to normalize.
     * @param bool $default Default when the value is absent or invalid.
     *
     * @return bool
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
}
