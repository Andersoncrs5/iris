<?php

namespace App\DTOs\Tag;

use App\Utils\Enums\Tag\TagColumnEnum;
use Illuminate\Http\Request;

readonly class FilterTagDTO
{
    public function __construct(
        public ?string $name = null,
        public bool $nameIgnoreCase = false,

        public ?string $slug = null,
        public bool $slugIgnoreCase = false,

        public ?string $color = null,
        public bool $colorIgnoreCase = false,

        public ?string $type = null,
        public ?string $description = null,

        public ?int $usageCountMin = null,
        public ?int $usageCountMax = null,

        public ?bool $isActive = null,

        public ?string $trashed = null, // 'with' | 'only' | null

        public int $perPage = 15,
        public TagColumnEnum $sortBy = TagColumnEnum::CREATED_AT,
        public string $sortOrder = 'desc',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $sortByInput = $request->query('sort_by');
        $sortBy = TagColumnEnum::tryFrom($sortByInput) ?? TagColumnEnum::CREATED_AT;

        $sortOrder = strtolower($request->query('sort_order', 'desc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'desc';

        return new self(
            name: $request->query('name'),
            nameIgnoreCase: $request->boolean('name_ignore_case', false),

            slug: $request->query('slug'),
            slugIgnoreCase: $request->boolean('slug_ignore_case', false),

            color: $request->query('color'),
            colorIgnoreCase: $request->boolean('color_ignore_case', false),

            type: $request->query('type'),
            description: $request->query('description'),

            usageCountMin: $request->filled('usage_count_min') ? (int) $request->query('usage_count_min') : null,
            usageCountMax: $request->filled('usage_count_max') ? (int) $request->query('usage_count_max') : null,

            isActive: $request->has('is_active') && $request->query('is_active') !== '' 
                ? $request->boolean('is_active') 
                : null,

            trashed: $request->query('trashed'),

            perPage: (int) $request->query('per_page', 15),
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        );
    }
}