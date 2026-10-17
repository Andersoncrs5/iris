<?php

namespace App\DTOs\TicketMessage;


final readonly class UpdateTicketMessageDTO
{
    /**
     * @param string $message Novo conteúdo da mensagem.
     */
    public function __construct(
        public readonly string $message,
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            message: $data['message'],
        );
    }

    public function toArray(): array
    {
        return [
            'message' => $this->message,
        ];
    }
}