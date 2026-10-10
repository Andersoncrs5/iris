<?php

namespace App\DTOs\User;

final readonly class UpdatePasswordDTO
{
    public function __construct(
        public string $password,
    ) {}

    public static function fromRequest(
        \App\Http\Requests\User\UserUpdatePasswordRequest $request
    ): self {
        return new self(
            password: $request->validated('password'),
        );
    }
}