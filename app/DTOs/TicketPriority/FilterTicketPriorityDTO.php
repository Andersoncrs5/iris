<?php

namespace App\DTOs\TicketPriority;

use App\Utils\Enums\TicketPriority\TicketPriorityColumnEnum;
use Illuminate\Http\Request;

final readonly class FilterTicketPriorityDTO
{
    public function __construct(
        public ?string $name = null,
        public bool $nameIgnoreCase = true,

        public ?string $code = null,
        public bool $codeIgnoreCase = true,

        public ?int $level = null,

        public ?string $description = null,
        public bool $descriptionIgnoreCase = true,

        public ?string $color = null,
        public bool $colorIgnoreCase = true,

        public ?string $icon = null,
        public bool $iconIgnoreCase = true,

        public ?int $responseTimeMinutesMin = null,
        public ?int $responseTimeMinutesMax = null,

        public ?int $resolutionTimeMinutesMin = null,
        public ?int $resolutionTimeMinutesMax = null,

        public ?int $escalationTimeMinutesMin = null,
        public ?int $escalationTimeMinutesMax = null,

        public ?bool $isDefault = null,
        public ?bool $isActive = null,
        public ?string $trashed = null,

        public int $perPage = 15,
        public TicketPriorityColumnEnum $sortBy = TicketPriorityColumnEnum::ID,
        public string $sortOrder = 'desc',
    ) {}

    /**
     * Create the DTO from the request query parameters.
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

        return new self(
            // Name
            name: self::nullableString(
                $request->input('name')
            ),
            nameIgnoreCase: self::booleanOrDefault(
                $request->input('name_ignore_case'),
                true
            ),

            // Code
            code: self::nullableString(
                $request->input('code')
            ),
            codeIgnoreCase: self::booleanOrDefault(
                $request->input('code_ignore_case'),
                true
            ),

            // Level
            level: self::nullableInteger(
                $request->input('level')
            ),

            // Description
            description: self::nullableString(
                $request->input('description')
            ),
            descriptionIgnoreCase: self::booleanOrDefault(
                $request->input('description_ignore_case'),
                true
            ),

            // Color
            color: self::nullableString(
                $request->input('color')
            ),
            colorIgnoreCase: self::booleanOrDefault(
                $request->input('color_ignore_case'),
                true
            ),

            // Icon
            icon: self::nullableString(
                $request->input('icon')
            ),
            iconIgnoreCase: self::booleanOrDefault(
                $request->input('icon_ignore_case'),
                true
            ),

            // Response time range
            responseTimeMinutesMin: self::nullableInteger(
                $request->input('response_time_minutes_min')
            ),
            responseTimeMinutesMax: self::nullableInteger(
                $request->input('response_time_minutes_max')
            ),

            // Resolution time range
            resolutionTimeMinutesMin: self::nullableInteger(
                $request->input('resolution_time_minutes_min')
            ),
            resolutionTimeMinutesMax: self::nullableInteger(
                $request->input('resolution_time_minutes_max')
            ),

            // Escalation time range
            escalationTimeMinutesMin: self::nullableInteger(
                $request->input('escalation_time_minutes_min')
            ),
            escalationTimeMinutesMax: self::nullableInteger(
                $request->input('escalation_time_minutes_max')
            ),

            // Boolean filters
            isDefault: self::nullableBoolean(
                $request->input('is_default')
            ),
            isActive: self::nullableBoolean(
                $request->input('is_active')
            ),

            // Soft Deletes
            trashed: $trashed,

            // Pagination
            perPage: max(1, min($perPage, 100)),

            // Sorting
            sortBy: TicketPriorityColumnEnum::safeTryFrom($sortByInput)
                ?? TicketPriorityColumnEnum::ID,

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
     * Normalize a value into a nullable integer.
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
     * Normalize a value into a nullable boolean.
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