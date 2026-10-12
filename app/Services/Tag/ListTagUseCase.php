<?php

namespace App\Services\Tag;

use App\DTOs\Tag\FilterTagDTO;
use App\Models\TagModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTagUseCase
{
    public function execute(FilterTagDTO $dto): LengthAwarePaginator
    {
        $query = TagModel::query();

        if ($dto->trashed === 'with') {
            $query->withTrashed();
        } elseif ($dto->trashed === 'only') {
            $query->onlyTrashed();
        }

        if ($dto->name !== null) {
            if ($dto->nameIgnoreCase) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%' . mb_strtolower($dto->name) . '%']);
            } else {
                $query->where('name', 'like', "%{$dto->name}%");
            }
        }

        if ($dto->slug !== null) {
            if ($dto->slugIgnoreCase) {
                $query->whereRaw('LOWER(slug) LIKE ?', ['%' . mb_strtolower($dto->slug) . '%']);
            } else {
                $query->where('slug', 'like', "%{$dto->slug}%");
            }
        }

        if ($dto->color !== null) {
            if ($dto->colorIgnoreCase) {
                $query->whereRaw('LOWER(color) LIKE ?', ['%' . mb_strtolower($dto->color) . '%']);
            } else {
                $query->where('color', $dto->color);
            }
        }

        if ($dto->type !== null) {
            $query->where('type', $dto->type);
        }

        if ($dto->description !== null) {
            $query->where('description', 'like', "%{$dto->description}%");
        }

        if ($dto->usageCountMin !== null) {
            $query->where('usage_count', '>=', $dto->usageCountMin);
        }
        if ($dto->usageCountMax !== null) {
            $query->where('usage_count', '<=', $dto->usageCountMax);
        }

        if ($dto->isActive !== null) {
            $query->where('is_active', $dto->isActive);
        }

        return $query
            ->orderBy($dto->sortBy->value, $dto->sortOrder)
            ->paginate($dto->perPage)
            ->withQueryString();
    }
}