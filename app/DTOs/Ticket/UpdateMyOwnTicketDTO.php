<?php

namespace App\DTOs\Ticket;

final readonly class UpdateMyOwnTicketDTO
{
    /**
     * @param array<string> $providedFields
     */
    public function __construct(
        public int $version,
        public string $subject,
        public string $description,
        public string $source,
        public ?int $satisfactionRating,
        public ?string $satisfactionComment,
        public ?bool $isEscalated,
        private array $providedFields = [],
    ) {}

    /**
     * Creates the DTO from validated request data.
     */
    public static function fromArray(array $data): self
    {
        $optionalFields = [
            'satisfaction_rating',
            'satisfaction_comment',
            'is_escalated',
        ];

        return new self(
            version: (int) $data['version'],

            subject: trim((string) $data['subject']),

            description: trim((string) $data['description']),

            source: strtoupper(trim((string) $data['source'])),

            satisfactionRating: self::nullableInteger(
                $data['satisfaction_rating'] ?? null
            ),

            satisfactionComment: self::nullableString(
                $data['satisfaction_comment'] ?? null
            ),

            isEscalated: array_key_exists('is_escalated', $data)
                ? self::toBoolean($data['is_escalated'])
                : null,

            providedFields: array_values(
                array_intersect($optionalFields, array_keys($data))
            ),
        );
    }

    /**
     * Checks whether an optional field was included in the request.
     */
    public function wasProvided(string $field): bool
    {
        return in_array($field, $this->providedFields, true);
    }

    /**
     * Returns only business fields allowed for the owner.
     *
     * The version is kept separate for concurrency control.
     */
    public function toArray(): array
    {
        $fields = [
            'subject' => $this->subject,
            'description' => $this->description,
            'source' => $this->source,
            'satisfaction_rating' => $this->satisfactionRating,
            'satisfaction_comment' => $this->satisfactionComment,
            'is_escalated' => $this->isEscalated,
        ];

        $requiredFields = [
            'subject',
            'description',
            'source',
        ];

        $allowedFields = array_merge(
            $requiredFields,
            $this->providedFields
        );

        return array_intersect_key(
            $fields,
            array_flip($allowedFields)
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

    private static function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN
        );
    }
}
