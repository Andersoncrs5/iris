<?php 

namespace App\Services\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;

class CreateCategoryUseCase
{
    public function __construct(
        private readonly ICategoryRepository $repository
    ) {}

    public function execute(CreateCategoryDTO $dto): CategoryModel
    {
        try {
            $data = $dto->toModel();

            $created = $this->repository->save($data);

            return $created;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}