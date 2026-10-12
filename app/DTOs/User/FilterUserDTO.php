<?php

namespace App\DTOs\User;

use App\Utils\Enums\User\UserColumnEnum;
use Illuminate\Http\Request;

final readonly class FilterUserDTO
{
    /**
     * @param int|null $id User Snowflake ID.
     * @param string|null $name User's display name.
     * @param bool $nameIgnoreCase Whether name matching ignores case.
     * @param string|null $fullName User's full name.
     * @param bool $fullNameIgnoreCase Whether full name matching ignores case.
     * @param string|null $email User's email address.
     * @param bool $emailIgnoreCase Whether email matching ignores case.
     * @param string|null $status Account status: ACTIVE, INACTIVE, or SUSPENDED.
     * @param bool|null $emailVerified Whether the email has been verified.
     * @param string|null $emailVerifiedAtFrom Verification date range start.
     * @param string|null $emailVerifiedAtTo Verification date range end.
     * @param string|null $lastLoginAtFrom Last login date range start.
     * @param string|null $lastLoginAtTo Last login date range end.
     * @param string|null $createdAtFrom Account creation date range start.
     * @param string|null $createdAtTo Account creation date range end.
     * @param string|null $updatedAtFrom Account update date range start.
     * @param string|null $updatedAtTo Account update date range end.
     * @param string|null $trashed Soft delete filter: with or only.
     * @param int $perPage Number of records per page.
     * @param UserColumnEnum $sortBy Allowed sorting column.
     * @param string $sortOrder Sorting direction: asc or desc.
     */
    public function __construct(
        public ?int $id = null,

        public ?string $name = null,
        public bool $nameIgnoreCase = true,

        public ?string $fullName = null,
        public bool $fullNameIgnoreCase = true,

        public ?string $email = null,
        public bool $emailIgnoreCase = true,

        public ?string $status = null,
        public ?bool $emailVerified = null,

        public ?string $emailVerifiedAtFrom = null,
        public ?string $emailVerifiedAtTo = null,

        public ?string $lastLoginAtFrom = null,
        public ?string $lastLoginAtTo = null,

        public ?string $createdAtFrom = null,
        public ?string $createdAtTo = null,

        public ?string $updatedAtFrom = null,
        public ?string $updatedAtTo = null,

        public ?string $trashed = null,

        public int $perPage = 15,
        public UserColumnEnum $sortBy = UserColumnEnum::ID,
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

        $statusInput = self::nullableString(
            $request->input('status')
        );

        $statusInput = $statusInput !== null
            ? strtoupper($statusInput)
            : null;

        $allowedStatuses = [
            'ACTIVE',
            'INACTIVE',
            'SUSPENDED',
        ];

        $status = in_array($statusInput, $allowedStatuses, true)
            ? $statusInput
            : null;

        return new self(
            // Identity
            id: self::nullableInteger(
                $request->input('id')
            ),

            // Name
            name: self::nullableString(
                $request->input('name')
            ),
            nameIgnoreCase: self::booleanOrDefault(
                $request->input('name_ignore_case'),
                true
            ),

            // Full name
            fullName: self::nullableString(
                $request->input('full_name')
            ),
            fullNameIgnoreCase: self::booleanOrDefault(
                $request->input('full_name_ignore_case'),
                true
            ),

            // Email
            email: self::nullableString(
                $request->input('email')
            ),
            emailIgnoreCase: self::booleanOrDefault(
                $request->input('email_ignore_case'),
                true
            ),

            // Account status
            status: $status,

            // Email verification
            emailVerified: self::nullableBoolean(
                $request->input('email_verified')
            ),

            emailVerifiedAtFrom: self::nullableString(
                $request->input('email_verified_at_from')
            ),
            emailVerifiedAtTo: self::nullableString(
                $request->input('email_verified_at_to')
            ),

            // Last login
            lastLoginAtFrom: self::nullableString(
                $request->input('last_login_at_from')
            ),
            lastLoginAtTo: self::nullableString(
                $request->input('last_login_at_to')
            ),

            // Account creation
            createdAtFrom: self::nullableString(
                $request->input('created_at_from')
            ),
            createdAtTo: self::nullableString(
                $request->input('created_at_to')
            ),

            // Account update
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
            sortBy: UserColumnEnum::safeTryFrom($sortByInput)
                ?? UserColumnEnum::ID,

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

        $integer = filter_var(
            $value,
            FILTER_VALIDATE_INT
        );

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
