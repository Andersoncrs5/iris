<?php

namespace App\Utils\Result;

class Result
{
    private function __construct(
        private readonly bool $isSuccess,
        private readonly mixed $value = null,
        private readonly ?string $error = null,
        private readonly mixed $code = null
    ) {}

    /**
     * Cria um resultado de sucesso.
     */
    public static function ok(mixed $value = null): self
    {
        return new self(isSuccess: true, value: $value);
    }


    /**
     * Cria um resultado de falha.
     */
    public static function fail(string $error, mixed $code = null): self
    {
        return new self(isSuccess: false, error: $error, code: $code);
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    public function isFailure(): bool
    {
        return !$this->isSuccess;
    }

    /**
     * Retorna o valor de sucesso ou dispara exceção se for uma falha.
     */
    public function getValue(): mixed
    {
        if ($this->isFailure()) {
            throw new \LogicException("Cannot retrieve value from a failed result.");
        }

        return $this->value;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getCode(): mixed
    {
        return $this->code;
    }
}