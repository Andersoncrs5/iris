<?php

namespace App\Services\Tag;

use App\DTOs\Tag\UpdateTagDTO;
use App\Models\TagModel;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\DB;
use Throwable;

class UpdateTagUseCase
{
    public function execute(UpdateTagDTO $dto, int|string $id): Result
    {
        try {
            return DB::transaction(function () use ($dto, $id): Result {
                $tag = TagModel::query()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if ($tag === null) {
                    return Result::fail('Tag not found.', 404);
                }

                $tag->fill([
                    'name' => $dto->name,
                    'slug' => $dto->slug,
                    'color' => $dto->color,
                    'type' => $dto->type,
                    'description' => $dto->description,
                    'is_active' => $dto->isActive,
                ]);

                $tag->save();

                return Result::ok($tag);
            }, 3);
        } catch (Throwable $th) {
            report($th);

            return Result::fail('Failed to update tag.');
        }
    }
}