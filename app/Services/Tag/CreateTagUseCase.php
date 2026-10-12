<?php

namespace App\Services\Tag;

use App\DTOs\Tag\CreateTagDTO;
use App\Models\TagModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;

class CreateTagUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(CreateTagDTO $dto): Result
    {
        $data = $dto->toModel();
        $data->id = $this->idGenerator->generate();

        $data->save();

        return Result::ok($data);
    }
    
}