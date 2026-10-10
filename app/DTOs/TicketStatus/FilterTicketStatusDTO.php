<?php

namespace App\DTOs\TicketStatus;

use App\Utils\Enums\TicketStatus\TicketStatusColumnEnum;
use Illuminate\Http\Request;

readonly class FilterTicketStatusDTO
{
    public function __construct(
        public ?string $name = null,
        public bool $nameIgnoreCase = false,

        public ?string $code = null,
        public bool $codeIgnoreCase = false,

        public ?string $description = null,

        public ?string $color = null,
        public bool $colorIgnoreCase = false,

        public ?string $icon = null,

        public ?int $displayOrderMin = null,
        public ?int $displayOrderMax = null,

        public ?bool $isInitial = null,
        public ?bool $isFinal = null,
        public ?bool $stopsSla = null,
        public ?bool $requiresComment = null,
        public ?bool $isActive = null,

        public ?string $trashed = null, // 'with' | 'only' | null

        public int $perPage = 15,
        public TicketStatusColumnEnum $sortBy = TicketStatusColumnEnum::DISPLAY_ORDER,
        public string $sortOrder = 'asc',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $sortByInput = $request->query('sort_by');
        $sortBy = TicketStatusColumnEnum::safeTryFrom($sortByInput) ?? TicketStatusColumnEnum::DISPLAY_ORDER;

        $sortOrder = strtolower((string) $request->query('sort_order', 'asc'));
        $sortOrder = in_array($sortOrder, ['asc', 'desc'], true) ? $sortOrder : 'asc';

        return new self(
            name: $request->query('name'),
            nameIgnoreCase: $request->boolean('name_ignore_case', false),

            code: $request->query('code'),
            codeIgnoreCase: $request->boolean('code_ignore_case', false),

            description: $request->query('description'),

            color: $request->query('color'),
            colorIgnoreCase: $request->boolean('color_ignore_case', false),

            icon: $request->query('icon'),

            displayOrderMin: $request->filled('display_order_min') ? (int) $request->query('display_order_min') : null,
            displayOrderMax: $request->filled('display_order_max') ? (int) $request->query('display_order_max') : null,

            isInitial: self::nullableBool($request, 'is_initial'),
            isFinal: self::nullableBool($request, 'is_final'),
            stopsSla: self::nullableBool($request, 'stops_sla'),
            requiresComment: self::nullableBool($request, 'requires_comment'),
            isActive: self::nullableBool($request, 'is_active'),

            trashed: $request->query('trashed'),

            perPage: $request->filled('per_page') ? (int) $request->query('per_page') : 15,
            sortBy: $sortBy,
            sortOrder: $sortOrder,
        );
    }

    private static function nullableBool(Request $request, string $key): ?bool
    {
        return $request->filled($key) ? $request->boolean($key) : null;
    }
}