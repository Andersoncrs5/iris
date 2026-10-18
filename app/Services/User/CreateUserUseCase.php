<?php

namespace App\Services\User;

use App\DTOs\User\UserRegisterDTO;
use App\Models\User;
use App\Repository\Interfaces\IUserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserUseCase
{
    public function __construct(
        private readonly IUserRepository $repository
    ) {}

    public function execute(UserRegisterDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            $user = new User();

            $user->name = $dto->name;
            $user->email = $dto->email;
            $user->full_name = $dto->fullName;
            $user->password = Hash::make($dto->password);

            return $this->repository->save($user);
        }, 3);
    }
}