<?php

namespace App\DTOs\TicketStatus;

use App\Http\Requests\TicketStatus\UpdateTicketStatusRequest;

readonly class UpdateTicketStatusDTO
{
    public function __construct(
        public int|string|null $id,
        public string $name,
        public string $code,
        public ?string $description = null,
        public ?string $color = null,
        public ?string $icon = null,
        public int $displayOrder = 0,
        public bool $isInitial = false,
        public bool $isFinal = false,
        public bool $stopsSla = false,
        public bool $requiresComment = false,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(UpdateTicketStatusRequest $request, int|string|null $id = null): self
    {
        return new self(
            id: $id ?? $request->route('id'),
            name: $request->validated('name'),
            code: $request->validated('code'),
            description: $request->validated('description'),
            color: $request->validated('color'),
            icon: $request->validated('icon'),
            displayOrder: (int) $request->validated('display_order', 0),
            isInitial: (bool) $request->validated('is_initial', false),
            isFinal: (bool) $request->validated('is_final', false),
            stopsSla: (bool) $request->validated('stops_sla', false),
            requiresComment: (bool) $request->validated('requires_comment', false),
            isActive: (bool) $request->validated('is_active', true),
        );
    }

    public static function fromArray(array $data, int|string|null $id = null): self
    {
        return new self(
            id: $id ?? ($data['id'] ?? null),
            name: $data['name'],
            code: $data['code'],
            description: $data['description'] ?? null,
            color: $data['color'] ?? null,
            icon: $data['icon'] ?? null,
            displayOrder: isset($data['display_order']) ? (int) $data['display_order'] : 0,
            isInitial: isset($data['is_initial']) ? (bool) $data['is_initial'] : false,
            isFinal: isset($data['is_final']) ? (bool) $data['is_final'] : false,
            stopsSla: isset($data['stops_sla']) ? (bool) $data['stops_sla'] : false,
            requiresComment: isset($data['requires_comment']) ? (bool) $data['requires_comment'] : false,
            isActive: isset($data['is_active']) ? (bool) $data['is_active'] : true,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'color' => $this->color,
            'icon' => $this->icon,
            'display_order' => $this->displayOrder,
            'is_initial' => $this->isInitial,
            'is_final' => $this->isFinal,
            'stops_sla' => $this->stopsSla,
            'requires_comment' => $this->requiresComment,
            'is_active' => $this->isActive,
        ];
    }
}