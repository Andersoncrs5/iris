<?php

namespace App\Services\Tag;

use App\Models\TagModel;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\Gate;

class DeleteTagUseCase 
{
    public function execute(int $id): Result
    {
        Gate::authorize('tag.delete');

        $tag = TagModel::find($id);

        if ($tag === null) {
            return Result::fail("Tag not found");
        }

        try {
            $tag->delete();
            return Result::ok();
        } catch (\Throwable $th) {
            return Result::fail("Failed to delete tag: " . $th->getMessage());
        }
    }
}