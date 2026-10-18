<?php

namespace App\Services\User;

use App\DTOs\User\UpdatePasswordDTO;
use App\Repository\Interfaces\IUserRepository;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordUseCase
{
    public function __construct(
        private readonly IUserRepository $repository,
        private readonly IdGeneratorInterface $idGenerator,
    ) {}

    public function execute(UpdatePasswordDTO $dto, int $userId): Result
    {
        if (!$this->idGenerator->isValid($userId)) {
            return Result::fail('Invalid user ID.', 400);
        }

        try {
            return DB::transaction(function () use ($dto, $userId): Result {
                $user = $this->repository->findById($userId);

                if ($user === null) {
                    return Result::fail('User not found.');
                }

                $user->password = Hash::make($dto->password);

                $result = $this->repository->save($user);

                return Result::ok($result);
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the password.'
            );
        }
    }
}