<?php

namespace App\DTOs\TicketPriority;

final readonly class CreateTicketPriorityDTO
{
    public function __construct(
        public string $name,
        public string $code,
        public int $level,
        public ?string $description = null,
        public ?string $color = null,
        public ?string $icon = null,
        public ?int $responseTimeMinutes = null,
        public ?int $resolutionTimeMinutes = null,
        public ?int $escalationTimeMinutes = null,
        public bool $isDefault = false,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: trim((string) $data['name']),
            code: trim((string) $data['code']),
            level: (int) $data['level'],
            description: self::nullableString($data['description'] ?? null),
            color: self::nullableString($data['color'] ?? null),
            icon: self::nullableString($data['icon'] ?? null),
            responseTimeMinutes: self::nullableInteger(
                $data['response_time_minutes'] ?? null
            ),
            resolutionTimeMinutes: self::nullableInteger(
                $data['resolution_time_minutes'] ?? null
            ),
            escalationTimeMinutes: self::nullableInteger(
                $data['escalation_time_minutes'] ?? null
            ),
            isDefault: self::toBoolean($data['is_default'] ?? false, false),
            isActive: self::toBoolean($data['is_active'] ?? true, true),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
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

        $integer = filter_var($value, FILTER_VALIDATE_INT);

        return $integer === false ? null : $integer;
    }

    private static function toBoolean(mixed $value, bool $default): bool
    {
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