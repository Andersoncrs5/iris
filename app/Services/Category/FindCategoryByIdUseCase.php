<?php 

namespace App\Services\Category;

use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Result\Result;

class FindCategoryByIdUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(?int $id): Result
    {
        if ($id === null) {
            return Result::fail("Id is required.", 400);
        }

        $category = $this->repository->findById($id);

        if (!$category) {
            return Result::fail("Category not found.", 404);
        }

        return Result::ok($category);
    }
}