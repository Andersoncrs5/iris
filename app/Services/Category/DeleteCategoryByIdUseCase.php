<?php

namespace App\Services\Category;

use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DeleteCategoryByIdUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(?int $id): Result
    {
        Gate::authorize('category.delete');

        if ($id === null || $id <= 0) {
            return Result::fail(
                'A valid category ID is required.',
                400
            );
        }

        try {
            return DB::transaction(function () use ($id): Result {
                $deleted = $this->repository->deleteById($id);

                if (!$deleted) {
                    return Result::fail(
                        'Category not found.',
                        404
                    );
                }

                return Result::ok($deleted);
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            return Result::fail(
                'An unexpected error occurred while deleting the category.',
                500
            );
        }
    }
}
