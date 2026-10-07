<?php

namespace App\DTOs\Category;

use App\Http\Requests\Category\UpdateCategoryRequest;

readonly class UpdateCategoryDTO
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?int $parentId = null,
        public ?string $icon = null,
        public int $displayOrder = 0,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(UpdateCategoryRequest $request, ?int $id = null): self
    {
        return new self(
            id: $id ?? ($request->route('id') ? (int) $request->route('id') : null),
            name: $request->validated('name'),
            slug: $request->validated('slug'),
            description: $request->validated('description'),
            parentId: $request->filled('parent_id') ? (int) $request->validated('parent_id') : null,
            icon: $request->validated('icon'),
            displayOrder: (int) $request->validated('display_order', 0),
            isActive: (bool) $request->validated('is_active', false),
        );
    }

    public static function fromArray(array $data, ?int $id = null): self
    {
        return new self(
            id: $id ?? ($data['id'] ?? null),
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            parentId: !empty($data['parent_id']) ? (int) $data['parent_id'] : null,
            icon: $data['icon'] ?? null,
            displayOrder: isset($data['display_order']) ? (int) $data['display_order'] : 0,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : false,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'parent_id' => $this->parentId,
            'icon' => $this->icon,
            'display_order' => $this->displayOrder,
            'is_active' => $this->isActive,
        ];
    }
}