<?php

namespace App\Services\Category;

use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class UpdateCategoryUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(UpdateCategoryDTO $dto, int $id): Result
    {
        Gate::authorize('category.update');

        if ($id <= 0) {
            return Result::fail(
                'A valid category ID is required.',
                400
            );
        }

        if ($dto->parentId !== null && $dto->parentId <= 0) {
            return Result::fail(
                'A valid parent category ID is required.',
                400
            );
        }

        try {
            return DB::transaction(function () use ($dto, $id): Result {
                $category = $this->repository->findById($id);

                if (!$category instanceof CategoryModel) {
                    return Result::fail(
                        'Category not found.',
                        404
                    );
                }

                if ($dto->parentId !== null) {
                    if ((string) $dto->parentId === (string) $category->getKey()) {
                        return Result::fail(
                            'A category cannot be its own parent.',
                            400
                        );
                    }

                    $parent = $this->repository->findById($dto->parentId);

                    if (!$parent instanceof CategoryModel) {
                        return Result::fail(
                            'Parent category not found.',
                            404
                        );
                    }

                    if ($this->wouldCreateCycle($category, $parent)) {
                        return Result::fail(
                            'The selected parent would create a category hierarchy cycle.',
                            400
                        );
                    }
                }

                $category->name = $dto->name;
                $category->slug = $dto->slug;
                $category->description = $dto->description;
                $category->parent_id = $dto->parentId;
                $category->icon = $dto->icon;
                $category->display_order = $dto->displayOrder;
                $category->is_active = $dto->isActive;

                $updated = $this->repository->update($category);

                if (!$updated) {
                    return Result::fail(
                        'Failed to update category.',
                        500
                    );
                }

                return Result::ok($updated);
            }, 3);
        } catch (Throwable $exception) {
            report($exception);

            return Result::fail(
                'An unexpected error occurred while updating the category.',
                500
            );
        }
    }

    private function wouldCreateCycle(
        CategoryModel $category,
        CategoryModel $parent
    ): bool {
        $categoryId = (string) $category->getKey();
        $visited = [];
        $current = $parent;

        while ($current !== null) {
            $currentId = (string) $current->getKey();

            if ($currentId === $categoryId) {
                return true;
            }

            if (isset($visited[$currentId])) {
                return true;
            }

            $visited[$currentId] = true;

            if ($current->parent_id === null) {
                return false;
            }

            $current = $this->repository->findById(
                (int) $current->parent_id
            );

            if ($current === null) {
                return false;
            }
        }

        return false;
    }
}
