<?php

namespace Tests\Unit\Services\User;

use App\DTOs\User\UserRegisterDTO;
use App\Models\User;
use App\Repository\Interfaces\IUserRepository;
use App\Services\User\CreateUserUseCase;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class CreateUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_creates_user_inside_transaction(): void
    {
        $dto = $this->makeDto();

        $repository = Mockery::mock(IUserRepository::class);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                static fn (Closure $callback, int $attempts) => $callback()
            );

        $savedUser = null;

        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class))
            ->andReturnUsing(function (User $user) use (&$savedUser): User {
                $savedUser = $user;

                return $user;
            });

        $result = (new CreateUserUseCase($repository))->execute($dto);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($result, $savedUser);
        $this->assertSame($dto->name, $result->name);
        $this->assertSame($dto->fullName, $result->full_name);
        $this->assertSame($dto->email, $result->email);

        $this->assertNotSame($dto->password, $result->password);
        $this->assertTrue(
            Hash::check($dto->password, $result->password),
            'The stored password must be a valid hash of the original password.'
        );
    }

    public function test_it_propagates_exception_when_repository_save_fails(): void
    {
        $dto = $this->makeDto();

        $repository = Mockery::mock(IUserRepository::class);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                static fn (Closure $callback, int $attempts) => $callback()
            );

        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class))
            ->andThrow(new RuntimeException('Repository save failed.'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Repository save failed.');

        (new CreateUserUseCase($repository))->execute($dto);
    }

    private function makeDto(): UserRegisterDTO
    {
        return new UserRegisterDTO(
            name: 'anderson',
            fullName: 'Anderson Carvalho',
            email: 'anderson@example.com',
            password: 'SecurePassword123!',
        );
    }
}