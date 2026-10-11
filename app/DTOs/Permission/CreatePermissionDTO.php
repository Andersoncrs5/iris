<?php

namespace App\DTOs\Permission;

use App\Http\Requests\Permission\CreatePermissionRequest;

readonly class CreatePermissionDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $module = 'system',
        public ?string $description = null,
    ) {}

    public static function fromRequest(CreatePermissionRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            module: $request->validated('module', 'system'),
            description: $request->validated('description'),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            module: $data['module'] ?? 'system',
            description: $data['description'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'module' => $this->module,
            'description' => $this->description,
        ];
    }
}