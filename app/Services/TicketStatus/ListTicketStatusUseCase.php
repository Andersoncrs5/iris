<?php

namespace App\Services\TicketStatus;

use App\DTOs\TicketStatus\FilterTicketStatusDTO;
use App\Models\TicketStatusModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTicketStatusUseCase 
{
    public function execute(FilterTicketStatusDTO $dto): LengthAwarePaginator
    {
        $query = TicketStatusModel::query();

        // Soft Deletes handling
        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        // Name filter
        if ($dto->name !== null && $dto->name !== '') {
            if ($dto->nameIgnoreCase) {
                $query->where('name', 'ilike', '%' . $dto->name . '%');
            } else {
                $query->where('name', 'like', '%' . $dto->name . '%');
            }
        }

        // Code filter
        if ($dto->code !== null && $dto->code !== '') {
            if ($dto->codeIgnoreCase) {
                $query->where('code', 'ilike', '%' . $dto->code . '%');
            } else {
                $query->where('code', 'like', '%' . $dto->code . '%');
            }
        }

        // Description filter
        if ($dto->description !== null && $dto->description !== '') {
            $query->where('description', 'ilike', '%' . $dto->description . '%');
        }

        // Color filter
        if ($dto->color !== null && $dto->color !== '') {
            if ($dto->colorIgnoreCase) {
                $query->where('color', 'ilike', '%' . $dto->color . '%');
            } else {
                $query->where('color', 'like', '%' . $dto->color . '%');
            }
        }

        // Icon filter
        if ($dto->icon !== null && $dto->icon !== '') {
            $query->where('icon', 'like', '%' . $dto->icon . '%');
        }

        // Display Order Range
        if ($dto->displayOrderMin !== null) {
            $query->where('display_order', '>=', $dto->displayOrderMin);
        }
        if ($dto->displayOrderMax !== null) {
            $query->where('display_order', '<=', $dto->displayOrderMax);
        }

        // Boolean filters
        if ($dto->isInitial !== null) {
            $query->where('is_initial', $dto->isInitial);
        }

        if ($dto->isFinal !== null) {
            $query->where('is_final', $dto->isFinal);
        }

        if ($dto->stopsSla !== null) {
            $query->where('stops_sla', $dto->stopsSla);
        }

        if ($dto->requiresComment !== null) {
            $query->where('requires_comment', $dto->requiresComment);
        }

        if ($dto->isActive !== null) {
            $query->where('is_active', $dto->isActive);
        }

        // Sorting & Pagination
        $query->orderBy($dto->sortBy->value, $dto->sortOrder);

        return $query->paginate($dto->perPage);
    }
}