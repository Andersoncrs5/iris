<?php

namespace App\DTOs\PermissionUser;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

readonly class FilterPermissionUserDTO
{
    public function __construct(
        public ?int $id = null,

        public ?int $userId = null,

        public ?string $userName = null,
        public bool $userNameIgnoreCase = false,

        public ?string $userEmail = null,
        public bool $userEmailIgnoreCase = false,

        public ?int $permissionId = null,

        public ?string $permissionName = null,
        public bool $permissionNameIgnoreCase = false,

        public ?string $permissionSlug = null,
        public bool $permissionSlugIgnoreCase = false,

        public ?string $permissionModule = null,

        public ?int $versionMin = null,
        public ?int $versionMax = null,

        public ?string $createdAtFrom = null,
        public ?string $createdAtTo = null,
        public ?string $updatedAtFrom = null,
        public ?string $updatedAtTo = null,

        public string $trashed = 'without',

        public int $perPage = 15,
        public string $sortBy = 'created_at',
        public string $sortOrder = 'desc',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'id' => ['nullable', 'integer', 'min:1'],

            'user_id' => ['nullable', 'integer', 'min:1'],
            'user_name' => ['nullable', 'string', 'max:255'],
            'user_name_ignore_case' => ['nullable', 'boolean'],
            'user_email' => ['nullable', 'string', 'max:255'],
            'user_email_ignore_case' => ['nullable', 'boolean'],

            'permission_id' => ['nullable', 'integer', 'min:1'],
            'permission_name' => ['nullable', 'string', 'max:255'],
            'permission_name_ignore_case' => ['nullable', 'boolean'],
            'permission_slug' => ['nullable', 'string', 'max:255'],
            'permission_slug_ignore_case' => ['nullable', 'boolean'],
            'permission_module' => ['nullable', 'string', 'max:255'],

            'version_min' => ['nullable', 'integer', 'min:1'],
            'version_max' => [
                'nullable',
                'integer',
                'min:1',
                'gte:version_min',
            ],

            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => [
                'nullable',
                'date',
                'after_or_equal:created_at_from',
            ],

            'updated_at_from' => ['nullable', 'date'],
            'updated_at_to' => [
                'nullable',
                'date',
                'after_or_equal:updated_at_from',
            ],

            'trashed' => [
                'nullable',
                Rule::in(['without', 'with', 'only']),
            ],

            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            'sort_by' => [
                'nullable',
                'string',
                Rule::in([
                    'id',
                    'user_id',
                    'permission_id',
                    'version',
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ]),
            ],

            'sort_order' => [
                'nullable',
                'string',
                Rule::in(['asc', 'desc']),
            ],
        ]);

        return new self(
            id: isset($validated['id'])
                ? (int) $validated['id']
                : null,

            userId: isset($validated['user_id'])
                ? (int) $validated['user_id']
                : null,

            userName: $validated['user_name'] ?? null,
            userNameIgnoreCase: (bool) ($validated['user_name_ignore_case'] ?? false),

            userEmail: $validated['user_email'] ?? null,
            userEmailIgnoreCase: (bool) ($validated['user_email_ignore_case'] ?? false),

            permissionId: isset($validated['permission_id'])
                ? (int) $validated['permission_id']
                : null,

            permissionName: $validated['permission_name'] ?? null,
            permissionNameIgnoreCase: (bool) ($validated['permission_name_ignore_case'] ?? false),

            permissionSlug: $validated['permission_slug'] ?? null,
            permissionSlugIgnoreCase: (bool) ($validated['permission_slug_ignore_case'] ?? false),

            permissionModule: $validated['permission_module'] ?? null,

            versionMin: isset($validated['version_min'])
                ? (int) $validated['version_min']
                : null,

            versionMax: isset($validated['version_max'])
                ? (int) $validated['version_max']
                : null,

            createdAtFrom: $validated['created_at_from'] ?? null,
            createdAtTo: $validated['created_at_to'] ?? null,

            updatedAtFrom: $validated['updated_at_from'] ?? null,
            updatedAtTo: $validated['updated_at_to'] ?? null,

            trashed: $validated['trashed'] ?? 'without',

            perPage: (int) ($validated['per_page'] ?? 15),
            sortBy: $validated['sort_by'] ?? 'created_at',
            sortOrder: $validated['sort_order'] ?? 'desc',
        );
    }
}