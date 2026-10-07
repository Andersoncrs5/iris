<?php

namespace App\DTOs\Tag;

use App\Http\Requests\Tag\UpdateTagRequest;

readonly class UpdateTagDTO
{
    public function __construct(
        public int|string|null $id,
        public string $name,
        public string $slug,
        public ?string $color = null,
        public string $type = 'GENERAL',
        public ?string $description = null,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(UpdateTagRequest $request, int|string|null $id = null): self
    {
        return new self(
            id: $id ?? $request->route('id'),
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            color: $request->validated('color'),
            type: $request->validated('type', 'GENERAL'),
            description: $request->validated('description'),
            isActive: (bool) $request->validated('is_active', false),
        );
    }

    public static function fromArray(array $data, int|string|null $id = null): self
    {
        return new self(
            id: $id ?? ($data['id'] ?? null),
            name: $data['name'],
            slug: $data['slug'],
            color: $data['color'] ?? null,
            type: $data['type'] ?? 'GENERAL',
            description: $data['description'] ?? null,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : false,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'slug' => $this->slug,
            'color' => $this->color,
            'type' => $this->type,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ], fn ($value) => $value !== null);
    }
}