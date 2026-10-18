<?php

namespace App\Services\User;

use App\DTOs\User\UpdateUserDTO;
use App\Models\User;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UpdateUserUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(UpdateUserDTO $dto, int $id): Result
    {
        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid user ID.', 400);
        }

        try {
            return DB::transaction(function () use ($dto, $id): Result {
                $user = User::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($user === null) {
                    return Result::fail('User not found.', 404);
                }

                $user->name = $dto->name;
                $user->full_name = $dto->fullName;

                $user->save();

                return Result::ok($user);
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the user.'
            );
        }
    }
}