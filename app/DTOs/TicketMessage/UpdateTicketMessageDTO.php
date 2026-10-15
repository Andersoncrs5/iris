<?php

namespace App\DTOs\Ticket;

use Illuminate\Http\Request;
use InvalidArgumentException;

final readonly class UpdateTicketMessageDTO
{
    /**
     * @param string $message Novo conteúdo da mensagem.
     * @param int $expectedVersion Versão que o cliente espera editar.
     */
    public function __construct(
        public string $message,
        public int $expectedVersion,
    ) {}

    /**
     * Cria o DTO a partir da requisição HTTP.
     *
     * Pressupõe que a requisição já passou pela validação.
     *
     * @param Request $request Requisição HTTP validada.
     *
     * @return self
     *
     * @throws InvalidArgumentException Quando a versão é inválida.
     */
    public static function fromRequest(Request $request): self
    {
        $version = filter_var(
            $request->input('expected_version'),
            FILTER_VALIDATE_INT,
        );

        if ($version === false || $version < 1) {
            throw new InvalidArgumentException(
                'The expected message version must be a positive integer.',
            );
        }

        return new self(
            message: trim((string) $request->input('message', '')),
            expectedVersion: $version,
        );
    }
}