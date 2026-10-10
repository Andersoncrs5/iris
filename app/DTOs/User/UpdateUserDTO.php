<?php

namespace App\DTOs\User;

use App\Http\Requests\User\UserUpdateRequest;

readonly class UpdateUserDTO
{
    public function __construct(
        public string $name,
        public string $fullName
    ) {}

    public static function fromRequest(UserUpdateRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            fullName: $validated['full_name']
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            fullName: $data['full_name']
        );
    }

    public function toArray(): array
    {
        return [
            'name'      => $this->name,
            'full_name' => $this->fullName
        ];
    }
}