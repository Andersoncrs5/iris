<?php 

namespace App\Repository;

use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Snowflake\IdGeneratorInterface;

class CategoryRepository implements ICategoryRepository 
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}
    
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