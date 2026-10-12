<?php

namespace App\Services\User;

use App\DTOs\User\UpdatePasswordDTO;
use App\Models\User;
use App\Repository\Interfaces\IUserRepository;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordUseCase
{
    public function __construct(
        private readonly IUserRepository $repository
    ) {}

    public function execute(UpdatePasswordDTO $dto, int $userId): Result
    {
        $user = $this->repository->findById($userId);

        if (!$user) {
            return Result::fail('User not found.');
        }

        $user->password = Hash::make($dto->password);

        $result = $this->repository->save($user);

        return Result::ok($result);
    }
}