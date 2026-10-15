<?php

namespace App\DTOs\Ticket;

use App\Utils\Enums\Ticket\TicketMessageTypeEnum;
use Illuminate\Http\Request;

final readonly class FilterTicketMessageDTO
{
    /**
     * @param int $ticketId ID do ticket.
     * @param int|null $userId Filtra pelo autor da mensagem.
     * @param TicketMessageTypeEnum|null $type Filtra pelo tipo.
     * @param bool|null $isInternal Filtra mensagens internas ou públicas.
     * @param int|null $parentId Filtra respostas de uma mensagem específica.
     * @param bool $topLevelOnly Retorna somente mensagens sem mensagem-pai.
     * @param int $perPage Quantidade de registros por página.
     * @param string $sortOrder Ordenação cronológica: asc ou desc.
     */
    public function __construct(
        public int $ticketId,
        public ?int $userId = null,
        public ?TicketMessageTypeEnum $type = null,
        public ?bool $isInternal = null,
        public ?int $parentId = null,
        public bool $topLevelOnly = false,
        public int $perPage = 30,
        public string $sortOrder = 'asc',
    ) {}

    /**
     * Cria o DTO a partir dos parâmetros da requisição.
     *
     * @param Request $request Requisição HTTP.
     * @param int $ticketId ID obtido da rota.
     *
     * @return self
     */
    public static function fromRequest(
        Request $request,
        int $ticketId,
    ): self {
        $userId = self::nullablePositiveInteger(
            $request->input('user_id'),
        );

        $parentId = self::nullablePositiveInteger(
            $request->input('parent_id'),
        );

        $typeInput = $request->input('type');

        $type = is_string($typeInput)
            ? TicketMessageTypeEnum::tryFrom(
                strtoupper(trim($typeInput)),
            )
            : null;

        $isInternal = null;

        if ($request->has('is_internal')) {
            $isInternal = filter_var(
                $request->input('is_internal'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE,
            );
        }

        $perPageInput = filter_var(
            $request->input('per_page', 30),
            FILTER_VALIDATE_INT,
        );

        $perPage = $perPageInput === false
            ? 30
            : max(1, min($perPageInput, 100));

        $sortOrder = strtolower(
            trim((string) $request->input('sort_order', 'asc')),
        );

        if (!in_array($sortOrder, ['asc', 'desc'], true)) {
            $sortOrder = 'asc';
        }

        return new self(
            ticketId: $ticketId,
            userId: $userId,
            type: $type,
            isInternal: $isInternal,
            parentId: $parentId,
            topLevelOnly: $request->boolean('top_level_only'),
            perPage: $perPage,
            sortOrder: $sortOrder,
        );
    }

    /**
     * Converte um valor em inteiro positivo ou null.
     *
     * @param mixed $value Valor recebido.
     *
     * @return int|null
     */
    private static function nullablePositiveInteger(
        mixed $value,
    ): ?int {
        if ($value === null || $value === '') {
            return null;
        }

        $integer = filter_var(
            $value,
            FILTER_VALIDATE_INT,
        );

        return $integer !== false && $integer > 0
            ? $integer
            : null;
    }
}