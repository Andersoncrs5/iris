<?php

namespace App\DTOs\Category;

use App\Http\Requests\Category\CreateCategoryRequest;
use App\Models\CategoryModel;

readonly class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?int $parentId = null,
        public ?string $icon = null,
        public int $displayOrder = 0,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(CreateCategoryRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            slug: $validated['slug'],
            description: $validated['description'] ?? null,
            parentId: isset($validated['parent_id']) ? (int) $validated['parent_id'] : null,
            icon: $validated['icon'] ?? null,
            displayOrder: isset($validated['display_order']) ? (int) $validated['display_order'] : 0,
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            description: $data['description'] ?? null,
            parentId: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            icon: $data['icon'] ?? null,
            displayOrder: isset($data['display_order']) ? (int) $data['display_order'] : 0,
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public static function fromModel(CategoryModel $model): self
    {
        return new self(
            name: $model->name,
            slug: $model->slug,
            description: $model->description,
            parentId: $model->parent_id,
            icon: $model->icon,
            displayOrder: $model->display_order,
            isActive: $model->is_active,
        );
    }

    public function toModel(): CategoryModel
    {
        $categoryModel = new CategoryModel();

        $categoryModel->name = $this->name;
        $categoryModel->slug = $this->slug;
        $categoryModel->description = $this->description;
        $categoryModel->parent_id = $this->parentId;
        $categoryModel->icon = $this->icon;
        $categoryModel->display_order = $this->displayOrder;
        $categoryModel->is_active = $this->isActive;

        return $categoryModel;
    }

    public function toArray(): array
    {
        return array_filter([
            'name'          => $this->name,
            'slug'          => $this->slug,
            'description'   => $this->description,
            'parent_id'     => $this->parentId,
            'icon'          => $this->icon,
            'display_order' => $this->displayOrder,
            'is_active'     => $this->isActive,
        ], fn ($value) => $value !== null);
    }
}