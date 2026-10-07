<?php

namespace App\DTOs\Category;

use App\Utils\Enums\Category\CategoryColumnEnum;
use Illuminate\Http\Request;

readonly class FilterCategoryDTO
{
    public function __construct(
        public ?string $name = null,
        public bool $nameIgnoreCase = false,

        public ?string $slug = null,
        public bool $slugIgnoreCase = false,

        public ?string $description = null,

        public ?string $icon = null,
        public bool $iconIgnoreCase = false,

        public ?int $displayOrderMin = null,
        public ?int $displayOrderMax = null,

        public ?bool $isActive = null,
        public ?int $parentId = null,

        public int $perPage = 15,
        public CategoryColumnEnum $sortBy = CategoryColumnEnum::CREATED_AT,
        public string $sortOrder = 'asc',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $sortByInput = $request->query('sort_by');
        $sortBy = CategoryColumnEnum::tryFrom($sortByInput) ?? CategoryColumnEnum::CREATED_AT;

        $sortOrder = strtolower($request->query('sort_order', 'asc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'asc';

        return new self(
            name: $request->query('name'),
            nameIgnoreCase: $request->boolean('name_ignore_case', false),

            slug: $request->query('slug'),
            slugIgnoreCase: $request->boolean('slug_ignore_case', false),

            description: $request->query('description'),

            icon: $request->query('icon'),
            iconIgnoreCase: $request->boolean('icon_ignore_case', false),

            displayOrderMin: $request->filled('display_order_min') ? (int) $request->query('display_order_min') : null,
            displayOrderMax: $request->filled('display_order_max') ? (int) $request->query('display_order_max') : null,

            isActive: $request->has('is_active') && $request->query('is_active') !== '' 
                ? $request->boolean('is_active') 
                : null,

            parentId: $request->filled('parent_id') ? (int) $request->query('parent_id') : null,

            perPage: (int) $request->query('per_page', 15),
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        );
    }
}