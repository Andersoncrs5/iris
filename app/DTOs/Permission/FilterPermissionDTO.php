<?php

namespace App\DTOs\Permission;

use App\Utils\Enums\Permission\PermissionColumnEnum;
use Illuminate\Http\Request;

readonly class FilterPermissionDTO
{
    public function __construct(
        public ?string $name = null,
        public bool $nameIgnoreCase = false,

        public ?string $slug = null,
        public bool $slugIgnoreCase = false,

        public ?string $module = null,
        public bool $moduleIgnoreCase = false,

        public ?string $description = null,

        public ?string $trashed = null, // 'with' | 'only' | null

        public int $perPage = 15,
        public PermissionColumnEnum $sortBy = PermissionColumnEnum::NAME,
        public string $sortOrder = 'asc',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $sortByInput = $request->query('sort_by');

        $sortBy = PermissionColumnEnum::safeTryFrom(
            is_string($sortByInput) ? $sortByInput : null
        ) ?? PermissionColumnEnum::NAME;

        $sortOrder = strtolower(
            (string) $request->query('sort_order', 'asc')
        );

        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true)
            ? $sortOrder
            : 'asc';

        $trashedInput = $request->query('trashed');

        $trashed = in_array($trashedInput, ['with', 'only'], true)
            ? $trashedInput
            : null;

        $perPageInput = $request->query('per_page', 15);

        $perPage = filter_var($perPageInput, FILTER_VALIDATE_INT);

        $perPage = $perPage !== false
            ? max(1, min($perPage, 100))
            : 15;

        return new self(
            name: $request->query('name'),
            nameIgnoreCase: $request->boolean('name_ignore_case'),

            slug: $request->query('slug'),
            slugIgnoreCase: $request->boolean('slug_ignore_case'),

            module: $request->query('module'),
            moduleIgnoreCase: $request->boolean('module_ignore_case'),

            description: $request->query('description'),

            trashed: $trashed,

            perPage: $perPage,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        );
    }

    private static function nullableBool(
        Request $request,
        string $key
    ): ?bool {
        return $request->filled($key)
            ? $request->boolean($key)
            : null;
    }
}