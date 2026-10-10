<?php

namespace App\DTOs\TicketStatus;

use App\Http\Requests\TicketStatus\CreateTicketStatusRequest;
use App\Models\TicketStatusModel;

readonly class CreateTicketStatusDTO
{
    public function __construct(
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

    public static function fromRequest(
        CreateTicketStatusRequest $request
    ): self {
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            code: $validated['code'],
            description: $validated['description'] ?? null,
            color: $validated['color'] ?? null,
            icon: $validated['icon'] ?? null,
            displayOrder: (int) ($validated['display_order'] ?? 0),
            isInitial: (bool) ($validated['is_initial'] ?? false),
            isFinal: (bool) ($validated['is_final'] ?? false),
            stopsSla: (bool) ($validated['stops_sla'] ?? false),
            requiresComment: (bool) ($validated['requires_comment'] ?? false),
            isActive: (bool) ($validated['is_active'] ?? true),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            code: $data['code'],
            description: $data['description'] ?? null,
            color: $data['color'] ?? null,
            icon: $data['icon'] ?? null,
            displayOrder: (int) ($data['display_order'] ?? 0),
            isInitial: (bool) ($data['is_initial'] ?? false),
            isFinal: (bool) ($data['is_final'] ?? false),
            stopsSla: (bool) ($data['stops_sla'] ?? false),
            requiresComment: (bool) ($data['requires_comment'] ?? false),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public static function fromModel(TicketStatusModel $model): self
    {
        return new self(
            name: $model->name,
            code: $model->code,
            description: $model->description,
            color: $model->color,
            icon: $model->icon,
            displayOrder: $model->display_order,
            isInitial: $model->is_initial,
            isFinal: $model->is_final,
            stopsSla: $model->stops_sla,
            requiresComment: $model->requires_comment,
            isActive: $model->is_active,
        );
    }

    public function toModel(): TicketStatusModel
    {
        $model = new TicketStatusModel();

        $model->name = $this->name;
        $model->code = $this->code;
        $model->description = $this->description;
        $model->color = $this->color;
        $model->icon = $this->icon;
        $model->display_order = $this->displayOrder;
        $model->is_initial = $this->isInitial;
        $model->is_final = $this->isFinal;
        $model->stops_sla = $this->stopsSla;
        $model->requires_comment = $this->requiresComment;
        $model->is_active = $this->isActive;

        return $model;
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