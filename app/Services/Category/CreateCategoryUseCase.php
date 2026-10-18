<?php

namespace App\Services\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use Illuminate\Support\Facades\DB;

class CreateCategoryUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(CreateCategoryDTO $dto): CategoryModel
    {
        return DB::transaction(function () use ($dto): CategoryModel {
            $data = $dto->toModel();

            return $this->repository->save($data);
        }, 3);
    }
}
