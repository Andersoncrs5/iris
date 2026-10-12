<?php

namespace App\Services\User;

use App\DTOs\User\UpdateUserDTO;
use App\Models\User;
use App\Utils\Result\Result;

class UpdateUserUseCase
{
    public function execute(UpdateUserDTO $dto, int $id): Result
    {
        $user = User::find($id);

        if (!$user) {
            return Result::fail("User not found.", 404);
        }

        $user->name = $dto->name;
        $user->full_name = $dto->fullName;

        $user->save();

        return Result::ok($user);
    }
}