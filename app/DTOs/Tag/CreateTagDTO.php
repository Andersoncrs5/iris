<?php

namespace App\DTOs\Tag;

use App\Http\Requests\Tag\CreateTagRequest;
use App\Models\TagModel;

readonly class CreateTagDTO
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $color = null,
        public string $type = 'GENERAL',
        public ?string $description = null,
        public int $usageCount = 0,
        public bool $isActive = true,
        public int $version = 1,
    ) {}

    public static function fromRequest(CreateTagRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            slug: $validated['slug'],
            color: $validated['color'] ?? null,
            type: $validated['type'] ?? 'GENERAL',
            description: $validated['description'] ?? null,
            usageCount: 0,
            isActive: (bool) ($validated['is_active'] ?? true),
            version: 1,
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            slug: $data['slug'],
            color: $data['color'] ?? null,
            type: $data['type'] ?? 'GENERAL',
            description: $data['description'] ?? null,
            usageCount: isset($data['usage_count']) ? (int) $data['usage_count'] : 0,
            isActive: (bool) ($data['is_active'] ?? true),
            version: isset($data['version']) ? (int) $data['version'] : 1,
        );
    }

    public static function fromModel(TagModel $model): self
    {
        return new self(
            name: $model->name,
            slug: $model->slug,
            color: $model->color,
            type: $model->type ?? 'GENERAL',
            description: $model->description,
            usageCount: $model->usage_count,
            isActive: $model->is_active,
            version: $model->version,
        );
    }

    public function toModel(): TagModel
    {
        $tagModel = new TagModel();

        $tagModel->name = $this->name;
        $tagModel->slug = $this->slug;
        $tagModel->color = $this->color;
        $tagModel->type = $this->type;
        $tagModel->description = $this->description;
        $tagModel->usage_count = $this->usageCount;
        $tagModel->is_active = $this->isActive;
        $tagModel->version = $this->version;

        return $tagModel;
    }

    public function toArray(): array
    {
        return array_filter([
            'name'        => $this->name,
            'slug'        => $this->slug,
            'color'       => $this->color,
            'type'        => $this->type,
            'description' => $this->description,
            'usage_count' => $this->usageCount,
            'is_active'   => $this->isActive,
            'version'     => $this->version,
        ], fn ($value) => $value !== null);
    }
}