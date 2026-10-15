<?php

namespace App\DTOs\TicketMessage;

use App\Utils\Enums\TicketMessage\TicketMessageTypeEnum;
use Illuminate\Http\Request;
use InvalidArgumentException;

final readonly class CreateTicketMessageDTO
{
    /**
     * @param int $ticketId ID do ticket ao qual a mensagem pertence.
     * @param string $message Conteúdo da mensagem.
     * @param TicketMessageTypeEnum $type Tipo da mensagem.
     * @param bool $isInternal Indica se a mensagem é interna.
     * @param int|null $parentId ID da mensagem respondida.
     * @param array<mixed>|null $attachments Anexos representados em array.
     */
    public function __construct(
        public int $ticketId,
        public string $message,
        public TicketMessageTypeEnum $type = TicketMessageTypeEnum::REPLY,
        public bool $isInternal = false,
        public ?int $parentId = null,
        public ?array $attachments = null,
    ) {}

    /**
     * Cria o DTO a partir dos dados validados da requisição.
     *
     * @param array{
     *     message: string,
     *     parent_id?: int|string|null
     * } $data Dados validados.
     * @param int $ticketId ID do ticket.
     *
     * @return self
     */
    public static function fromValidated(
        array $data,
        int $ticketId,
    ): self {
        $parentId = $data['parent_id'] ?? null;

        return new self(
            ticketId: $ticketId,
            message: trim($data['message']),
            type: TicketMessageTypeEnum::REPLY,
            isInternal: false,
            parentId: $parentId !== null
                ? (int) $parentId
                : null,
            attachments: null,
        );
    }

    /**
     *
     * @param Request $request Requisição HTTP validada.
     * @param int $ticketId ID obtido da rota.
     *
     * @return self
     *
     * @throws InvalidArgumentException 
     */
    public static function fromRequest(
        Request $request,
        int $ticketId,
    ): self {
        $typeInput = strtoupper(
            trim((string) $request->input(
                'type',
                TicketMessageTypeEnum::REPLY->value,
            )),
        );

        $type = TicketMessageTypeEnum::tryFrom($typeInput);

        if (
            $type === null ||
            $type === TicketMessageTypeEnum::SYSTEM_EVENT
        ) {
            throw new InvalidArgumentException(
                'Invalid message type for user-created messages.',
            );
        }

        $parentId = $request->input('parent_id');
        $attachments = $request->input('attachments');

        return new self(
            ticketId: $ticketId,
            message: trim((string) $request->input('message', '')),
            type: $type,
            isInternal: $request->boolean('is_internal'),
            parentId: $parentId !== null
                ? (int) $parentId
                : null,
            attachments: is_array($attachments)
                ? $attachments
                : null,
        );
    }
}