<?php

namespace App\Services\Tag;

use App\Models\TagModel;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DeleteTagUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('tag.delete');

        try {
            return DB::transaction(function () use ($id): Result {
                $tag = TagModel::find($id);

                if ($tag === null) {
                    return Result::fail('Tag not found');
                }

                $tag->delete();

                return Result::ok();
            }, 3);
        } catch (Throwable $th) {
            return Result::fail(
                'Failed to delete tag: ' . $th->getMessage()
            );
        }
    }
}