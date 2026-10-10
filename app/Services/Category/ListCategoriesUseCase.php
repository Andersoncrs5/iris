<?php

namespace App\Services\Category;

use App\DTOs\Category\FilterCategoryDTO;
use App\Repository\Interfaces\ICategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCategoriesUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(FilterCategoryDTO $dto): LengthAwarePaginator
    {
        return $this->repository->paginate($dto);
    }
}