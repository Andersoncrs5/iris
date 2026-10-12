<?php

namespace App\Services\Pemission;

use App\DTOs\Permission\CreatePermissionDTO;
use App\Models\PermissionModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;

class CreatePermissionUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(CreatePermissionDTO $dto): Result
    {
        try {
            $permission = PermissionModel::create([
                'id' => $this->idGenerator->generate(),
                'name' => $dto->name,
                'slug' => $dto->slug,
                'module' => $dto->module,
                'description' => $dto->description,
            ]);

            return Result::ok($permission);
        } catch (\Throwable $th) {
            return Result::fail("Failed to create permission: " . $th->getMessage());
        }
    }
}