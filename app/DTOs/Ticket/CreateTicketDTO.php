<?php

namespace App\DTOs\Ticket;

final readonly class CreateTicketDTO
{
    /**
     * @param int $userId ID do usuário que abriu o chamado.
     * @param int|null $agentId ID do agente inicialmente atribuído.
     * @param int|null $teamId ID da equipe inicialmente atribuída.
     * @param int $categoryId ID da categoria do chamado.
     * @param int $priorityId ID da prioridade do chamado.
     * @param string $subject Título do chamado.
     * @param string $description Descrição inicial do chamado.
     * @param string $source Canal de origem do chamado.
     */
    public function __construct(
        public int $userId,
        public ?int $agentId,
        public ?int $teamId,
        public int $categoryId,
        public int $priorityId,
        public string $subject,
        public string $description,
        public string $source = 'WEB',
    ) {}

    /**
     * Create the DTO from validated request data.
     *
     * The authenticated user's ID must be supplied by the server
     * rather than trusted directly from a client-submitted field.
     *
     * @param array<string, mixed> $data Validated request data.
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            agentId: self::nullableInteger($data['agent_id'] ?? null),
            teamId: self::nullableInteger($data['team_id'] ?? null),
            categoryId: (int) $data['category_id'],
            priorityId: (int) $data['priority_id'],
            subject: trim((string) $data['subject']),
            description: trim((string) $data['description']),
            source: strtoupper(trim((string) ($data['source'] ?? 'WEB'))),
        );
    }

    /**
     * Convert the DTO into model attribute names.
     *
     * System-managed fields such as the Snowflake ID, ticket number,
     * initial status, version, and timestamps are excluded.
     *
     * @return array{
     *     user_id: int,
     *     agent_id: int|null,
     *     team_id: int|null,
     *     category_id: int,
     *     priority_id: int,
     *     subject: string,
     *     description: string,
     *     source: string
     * }
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'agent_id' => $this->agentId,
            'team_id' => $this->teamId,
            'category_id' => $this->categoryId,
            'priority_id' => $this->priorityId,
            'subject' => $this->subject,
            'description' => $this->description,
            'source' => $this->source,
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
