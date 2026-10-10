<?php 

namespace App\Services\Category;

use App\DTOs\Category\UpdateCategoryDTO;
use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Result\Result;

class UpdateCategoryUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(UpdateCategoryDTO $dto, int $id): Result
    {
        $category = $this->repository->findById($id);

        if (!$category) {
            return Result::fail("Category not found.", 404);
        }

        $category->name = $dto->name;
        $category->slug = $dto->slug;
        $category->description = $dto->description;
        $category->parent_id = $dto->parentId;
        $category->icon = $dto->icon;
        $category->display_order = $dto->displayOrder;
        $category->is_active = $dto->isActive;

        $result = $this->repository->update($category);

        return Result::ok($result);
    }
}