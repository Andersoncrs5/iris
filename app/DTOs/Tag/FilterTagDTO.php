<?php

namespace App\DTOs\Tag;

use App\Utils\Enums\Tag\TagColumnEnum;
use Illuminate\Http\Request;

final readonly class FilterTagDTO
{
    public function __construct(
        public ?string $name = null,
        public bool $nameIgnoreCase = false,

        public ?string $slug = null,
        public bool $slugIgnoreCase = false,

        public ?string $color = null,
        public bool $colorIgnoreCase = false,

        /** @var list<string>|null */
        public ?array $type = [],

        public ?string $description = null,
        public bool $descriptionIgnoreCase = false,

        public ?int $usageCountMin = null,
        public ?int $usageCountMax = null,

        public ?bool $isActive = null,

        public ?string $trashed = null,

        public int $perPage = 15,
        public TagColumnEnum $sortBy = TagColumnEnum::CREATED_AT,
        public string $sortOrder = 'desc',
    ) {}

    /**
     * Create the DTO from request query parameters.
     */
    public static function fromRequest(Request $request): self
    {
        $sortByInput = self::nullableString(
            $request->query('sort_by')
        );

        $sortBy = $sortByInput !== null
            ? TagColumnEnum::tryFrom($sortByInput)
            : null;

        $sortOrder = strtolower(
            trim((string) $request->query('sort_order', 'desc'))
        );

        $perPage = filter_var(
            $request->query('per_page', 15),
            FILTER_VALIDATE_INT
        );

        if ($perPage === false) {
            $perPage = 15;
        }

        $trashedInput = self::nullableString(
            $request->query('trashed')
        );

        $trashed = in_array(
            $trashedInput,
            ['with', 'only'],
            true
        )
            ? $trashedInput
            : null;

        return new self(
            // Name
            name: self::nullableString(
                $request->query('name')
            ),

            nameIgnoreCase: self::booleanOrDefault(
                $request->query('name_ignore_case'),
                false
            ),

            // Slug
            slug: self::nullableString(
                $request->query('slug')
            ),

            slugIgnoreCase: self::booleanOrDefault(
                $request->query('slug_ignore_case'),
                false
            ),

            // Color
            color: self::nullableString(
                $request->query('color')
            ),

            colorIgnoreCase: self::booleanOrDefault(
                $request->query('color_ignore_case'),
                false
            ),

            // One or multiple tag types
            type: self::normalizeTypes(
                $request->query('type')
            ),

            // Description
            description: self::nullableString(
                $request->query('description')
            ),

            descriptionIgnoreCase: self::booleanOrDefault(
                $request->query('description_ignore_case'),
                false
            ),

            // Usage count range
            usageCountMin: self::nullableNonNegativeInteger(
                $request->query('usage_count_min')
            ),

            usageCountMax: self::nullableNonNegativeInteger(
                $request->query('usage_count_max')
            ),

            // Active state
            isActive: self::nullableBoolean(
                $request->query('is_active')
            ),

            // Soft deletes
            trashed: $trashed,

            // Pagination
            perPage: max(1, min($perPage, 100)),

            // Sorting
            sortBy: $sortBy ?? TagColumnEnum::CREATED_AT,

            sortOrder: in_array(
                $sortOrder,
                ['asc', 'desc'],
                true
            )
                ? $sortOrder
                : 'desc',
        );
    }

    /**
     * Normalize one or multiple type values.
     *
     * @return list<string>
     */
    private static function normalizeTypes(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        $values = is_array($value) ? $value : [$value];
        $types = [];

        foreach ($values as $item) {
            $type = self::nullableString($item);

            if ($type !== null) {
                $types[] = $type;
            }
        }

        return array_values(array_unique($types));
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
     * Normalize a non-negative integer.
     */
    private static function nullableNonNegativeInteger(
        mixed $value
    ): ?int {
        $integer = self::nullableInteger($value);

        return $integer !== null && $integer >= 0
            ? $integer
            : null;
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
     * Normalize a boolean value with a default.
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