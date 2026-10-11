<?php 

namespace App\Services\Tag;

use App\Models\TagModel;
use App\Utils\Result\Result;

class ToggleTagUseCase
{
    public function execute(int $id): Result
    {
        if ($id == null) {
            return Result::fail("Id should be required", 400);
        }

        $tag = TagModel::find($id);

        if (!$tag) {
            return Result::fail("Category not found.", 404);
        }

        $tag->is_active = !$tag->is_active;
        $tag->save();

        return Result::ok($tag);
    }
}