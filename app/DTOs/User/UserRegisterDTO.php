<?php

namespace App\DTOs\User;

use App\Http\Requests\User\UserRegisterRequest;

readonly class UserRegisterDTO
{
    public function __construct(
        public string $name,
        public string $fullName,
        public string $email,
        public string $password,
    ) {}

    public static function fromRequest(UserRegisterRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            fullName: $validated['full_name'],
            email: $validated['email'],
            password: $validated['password'],
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            fullName: $data['full_name'],
            email: $data['email'],
            password: $data['password'],
        );
    }

    public function toArray(): array
    {
        return [
            'name'      => $this->name,
            'full_name' => $this->fullName,
            'email'     => $this->email,
            'password'  => $this->password,
        ];
    }
}