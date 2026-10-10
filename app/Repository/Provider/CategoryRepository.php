<?php 

namespace App\Repository\Provider;

use App\DTOs\Category\FilterCategoryDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Snowflake\IdGeneratorInterface;

class CategoryRepository implements ICategoryRepository 
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function paginate(FilterCategoryDTO $dto): LengthAwarePaginator
    {
        $query = CategoryModel::query()->with('parent');

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

        if ($dto->description !== null) {
            $query->where('description', 'like', "%{$dto->description}%");
        }

        if ($dto->icon !== null) {
            if ($dto->iconIgnoreCase) {
                $query->whereRaw('LOWER(icon) LIKE ?', ['%' . mb_strtolower($dto->icon) . '%']);
            } else {
                $query->where('icon', $dto->icon);
            }
        }

        if ($dto->displayOrderMin !== null) {
            $query->where('display_order', '>=', $dto->displayOrderMin);
        }
        if ($dto->displayOrderMax !== null) {
            $query->where('display_order', '<=', $dto->displayOrderMax);
        }

        if ($dto->isActive !== null) {
            $query->where('is_active', $dto->isActive);
        }
        if ($dto->parentId !== null) {
            $query->where('parent_id', $dto->parentId);
        }

        return $query
            ->orderBy($dto->sortBy->value, $dto->sortOrder)
            ->paginate($dto->perPage)
            ->withQueryString();
    }
    
    public function count(): int
    {
        return CategoryModel::count();
    }
    
    public function findById(int $id): ?CategoryModel 
    {
        return CategoryModel::find($id);
    }

    public function findByIdForUpdate(int $id): ?CategoryModel 
    {
        return CategoryModel::where('id', $id)->lockForUpdate()->first();
    }

    public function save(CategoryModel $category): CategoryModel
    {
        if (!$category->exists && !$category->id) {
            $category->id = $this->idGenerator->generate();
        }

        $category->save();
        return $category;
    }

    public function update(CategoryModel $category): CategoryModel
    {
        $category->save();
        return $category;
    }

    public function existsById(int $id): bool
    {
        return CategoryModel::where('id', $id)->exists();
    }

    public function deleteById(int $id): bool
    {
        return CategoryModel::destroy($id) > 0;
    }

    public function deleteByIdForce(int $id): bool
    {
        return CategoryModel::withTrashed()->where('id', $id)->forceDelete() > 0;
    }

    public function deleteAllForce(): int
    {
        return CategoryModel::withTrashed()->forceDelete();
    }
}