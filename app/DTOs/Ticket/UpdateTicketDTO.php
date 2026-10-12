<?php

namespace App\DTOs\Ticket;

final readonly class UpdateTicketDTO
{
    /**
     * @param int $version Version read by the client before editing.
     * @param int $categoryId ID of the ticket category.
     * @param int $priorityId ID of the ticket priority.
     * @param string $subject Ticket subject.
     * @param string $description Ticket description.
     * @param int|null $agentId Assigned agent ID.
     * @param int|null $teamId Assigned team ID.
     */
    public function __construct(
        public int $version,
        public string $subject,
        public string $description,
        public int $categoryId,
        public int $priorityId,
        public ?int $agentId = null,
        public ?int $teamId = null,
    ) {}

    /**
     * Create the DTO from validated request data.
     *
     * @param array<string, mixed> $data Validated request data.
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            version: (int) $data['version'],
            subject: trim((string) $data['subject']),
            description: trim((string) $data['description']),
            categoryId: (int) $data['category_id'],
            priorityId: (int) $data['priority_id'],
            agentId: self::nullableInteger($data['agent_id'] ?? null),
            teamId: self::nullableInteger($data['team_id'] ?? null),
        );
    }

    /**
     * Convert the DTO into model attribute names.
     *
     * The version is excluded because the use case must compare it
     * against the persisted version and increment it itself.
     *
     * @return array{
     *     subject: string,
     *     description: string,
     *     category_id: int,
     *     priority_id: int,
     *     agent_id: int|null,
     *     team_id: int|null
     * }
     */
    public function toArray(): array
    {
        return [
            'subject' => $this->subject,
            'description' => $this->description,
            'category_id' => $this->categoryId,
            'priority_id' => $this->priorityId,
            'agent_id' => $this->agentId,
            'team_id' => $this->teamId,
        ];
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
}
