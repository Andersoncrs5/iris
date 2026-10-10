<?php 

namespace App\Services\Category;

use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\Gate;

class DeleteCategoryByIdUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(?int $id): Result
    {
        Gate::authorize('category.delete');
        
        if ($id === null) {
            return Result::fail("Id is required.", 400);
        }

        $result = $this->repository->deleteById($id);

        if (!$result) {
            return Result::fail("Category not found.", 404);
        }

        return Result::ok($result);
    }
}