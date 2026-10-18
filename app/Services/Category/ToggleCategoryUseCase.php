
<?php

namespace App\Services\Category;

use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ToggleCategoryUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(int $id): Result
    {
        Gate::authorize('category.update');

        if ($id <= 0) {
            return Result::fail(
                'A valid category ID is required.',
                400
            );
        }

        try {
            return DB::transaction(function () use ($id): Result {
                $category = $this->repository->findById($id);

                if (!$category instanceof CategoryModel) {
                    return Result::fail(
                        'Category not found.',
                        404
                    );
                }

                $category->is_active = !(bool) $category->is_active;

                $updated = $this->repository->update($category);

                if ($updated === false) {
                    return Result::fail(
                        'Failed to update category status.',
                        500
                    );
                }

                return Result::ok($updated);
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            return Result::fail(
                'An unexpected error occurred while updating the category status.',
                500
            );
        }
    }
}
