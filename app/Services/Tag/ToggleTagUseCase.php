<?php

namespace App\Services\Tag;

use App\Models\TagModel;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\DB;

class ToggleTagUseCase
{
    public function execute(int $id): Result
    {
        if ($id <= 0) {
            return Result::fail('Id should be required', 400);
        }

        return DB::transaction(function () use ($id): Result {
            $tag = TagModel::find($id);

            if ($tag === null) {
                return Result::fail('Tag not found.', 404);
            }

            $tag->is_active = !(bool) $tag->is_active;
            $tag->save();

            return Result::ok($tag);
        }, 3);
    }
}