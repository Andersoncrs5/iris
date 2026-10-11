<?php

namespace App\DTOs\Permission;

use App\Http\Requests\Permission\UpdatePermissionRequest;

readonly class UpdatePermissionDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public string $module,
        public ?string $description = null,
    ) {}

    public static function fromRequest(
        UpdatePermissionRequest $request
    ): self {
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            slug: $validated['slug'],
            module: $validated['module'],
            description: $validated['description'] ?? null,
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