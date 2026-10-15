<?php

namespace App\DTOs\Ticket;

use App\Models\TicketMessageModel;
use App\Utils\Enums\Ticket\TicketMessageTypeEnum;
use Illuminate\Support\Carbon;

final readonly class TicketMessageDTO
{
    /**
     * @param int $id Identificador da mensagem.
     * @param int $ticketId ID do ticket.
     * @param int $userId ID do autor.
     * @param int|null $parentId ID da mensagem-pai.
     * @param string $message Conteúdo da mensagem.
     * @param TicketMessageTypeEnum $type Tipo da mensagem.
     * @param bool $isInternal Indica se é uma mensagem interna.
     * @param bool $isEdited Indica se foi editada.
     * @param Carbon|null $editedAt Data da última edição.
     * @param array<mixed>|null $attachments Anexos da mensagem.
     * @param int $version Versão atual da mensagem.
     * @param Carbon|null $createdAt Data de criação.
     * @param Carbon|null $updatedAt Data de atualização.
     */
    public function __construct(
        public int $id,
        public int $ticketId,
        public int $userId,
        public ?int $parentId,
        public string $message,
        public TicketMessageTypeEnum $type,
        public bool $isInternal,
        public bool $isEdited,
        public ?Carbon $editedAt,
        public ?array $attachments,
        public int $version,
        public ?Carbon $createdAt,
        public ?Carbon $updatedAt,
    ) {}

    /**
     * Cria o DTO a partir do model Eloquent.
     *
     * @param TicketMessageModel $model Mensagem persistida.
     *
     * @return self
     */
    public static function fromModel(
        TicketMessageModel $model,
    ): self {
        $type = $model->type instanceof TicketMessageTypeEnum
            ? $model->type
            : TicketMessageTypeEnum::from($model->type);

        return new self(
            id: (int) $model->id,
            ticketId: (int) $model->ticket_id,
            userId: (int) $model->user_id,
            parentId: $model->parent_id !== null
                ? (int) $model->parent_id
                : null,
            message: $model->message,
            type: $type,
            isInternal: (bool) $model->is_internal,
            isEdited: (bool) $model->is_edited,
            editedAt: $model->edited_at,
            attachments: $model->attachments,
            version: (int) $model->version,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
        );
    }
}