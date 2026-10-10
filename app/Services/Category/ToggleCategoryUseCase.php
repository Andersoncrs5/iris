<?php 

namespace App\Services\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Result\Result;

class ToggleCategoryUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(int $id): Result
    {
        if ($id == null) {
            return Result::fail("Id should be required", 400);
        }

        $category = CategoryModel::find($id);

        if (!$category) {
            return Result::fail("Category not found.", 404);
        }

        $category->is_active = !$category->is_active;
        $updated = $this->repository->update($category);

        return Result::ok($updated);
    }
}