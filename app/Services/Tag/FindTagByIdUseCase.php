<?php

namespace App\Services\Tag;

use App\Models\TagModel;
use App\Utils\Result\Result;

class FindTagByIdUseCase
{
    public function execute(int $id): Result 
    {
        $tag = TagModel::find($id);

        if ($tag == null) {
            return Result::fail("Tag not found");
        }

        return Result::ok($tag);
    }
    
}