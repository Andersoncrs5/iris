<?php

namespace App\Services\Tag;

use App\DTOs\Tag\UpdateTagDTO;
use App\Models\TagModel;
use App\Utils\Result\Result;

class UpdateTagUseCase
{
    public function execute(UpdateTagDTO $dto, int|string $id): Result
    {
        $tag = TagModel::find($id);

        if (!$tag) {
            return Result::fail("Tag not found.", 404);
        }

        $tag->name = $dto->name;
        $tag->slug = $dto->slug;
        $tag->color = $dto->color;
        $tag->type = $dto->type;
        $tag->description = $dto->description;
        $tag->is_active = $dto->isActive;

        $tag->save();

        return Result::ok($tag);
    }
}