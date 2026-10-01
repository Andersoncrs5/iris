<?php 

namespace App\Repository\Interfaces;

use App\Models\CategoryModel;

interface ICategoryRepository 
{
    public function findById(int $id): ?CategoryModel;
    public function findByIdForUpdate(int $id): ?CategoryModel;
    public function save(CategoryModel $category): CategoryModel;
    public function update(CategoryModel $category): CategoryModel;

    public function existsById(int $id): bool;
    public function deleteById(int $id): bool;
    public function deleteByIdForce(int $id): bool;
    public function deleteAllForce(): int;

    public function count(): int;
}