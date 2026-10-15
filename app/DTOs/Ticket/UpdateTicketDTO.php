<?php

namespace App\DTOs\Ticket;

final readonly class UpdateTicketDTO
{
    /**
     * @param array<string> $providedFields
     */
    public function __construct(
        public int $version,
        public ?int $statusId,
        public ?string $firstResponseAt,
        public ?string $resolvedAt,
        public ?string $closedAt,
        private array $providedFields = [],
    ) {}

    /**
     * Creates the DTO from validated request data.
     */
    public static function fromArray(array $data): self
    {
        $editableFields = [
            'status_id',
            'first_response_at',
            'resolved_at',
            'closed_at',
        ];

        return new self(
            version: (int) $data['version'],

            statusId: array_key_exists('status_id', $data)
                ? (int) $data['status_id']
                : null,

            firstResponseAt: self::nullableString(
                $data['first_response_at'] ?? null
            ),

            resolvedAt: self::nullableString(
                $data['resolved_at'] ?? null
            ),

            closedAt: self::nullableString(
                $data['closed_at'] ?? null
            ),

            providedFields: array_values(
                array_intersect($editableFields, array_keys($data))
            ),
        );
    }

    /**
     * Checks whether a field was included in the request.
     */
    public function wasProvided(string $field): bool
    {
        return in_array($field, $this->providedFields, true);
    }

    /**
     * Returns only fields provided for updating.
     *
     * The version is kept separate for concurrency control.
     */
    public function toArray(): array
    {
        $fields = [
            'status_id' => $this->statusId,
            'first_response_at' => $this->firstResponseAt,
            'resolved_at' => $this->resolvedAt,
            'closed_at' => $this->closedAt,
        ];

        return array_intersect_key(
            $fields,
            array_flip($this->providedFields)
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
}
