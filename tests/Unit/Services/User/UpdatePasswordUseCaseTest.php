<?php

namespace Tests\Unit\Services\User;

use App\DTOs\User\UpdatePasswordDTO;
use App\Models\User;
use App\Repository\Interfaces\IUserRepository;
use App\Services\User\UpdatePasswordUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use Tests\TestCase;

class UpdatePasswordUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_updates_password_inside_transaction(): void
    {
        $userId = 123;
        $newPassword = 'NewSecurePassword123!';

        $dto = new UpdatePasswordDTO(password: $newPassword);

        $user = new User();
        $user->name = 'Anderson';
        $user->email = 'anderson@example.com';
        $user->password = 'OldSecurePassword123!';

        $repository = Mockery::mock(IUserRepository::class);
        $idGenerator = $this->mockIdValidation($userId, true);

        $repository->shouldReceive('findById')
            ->once()
            ->with($userId)
            ->andReturn($user);

        $repository->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class))
            ->andReturnUsing(
                static fn (User $savedUser) => $savedUser
            );

        $this->mockTransaction();

        $result = (new UpdatePasswordUseCase(
            $repository,
            $idGenerator
        ))->execute($dto, $userId);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Password update failed.'
        );

        $this->assertSame($user, $result->getValue());
        $this->assertNotSame($newPassword, $user->password);

        $this->assertTrue(
            Hash::check($newPassword, $user->password),
            'The updated password must be stored as a valid hash.'
        );
    }

    public function test_it_rejects_invalid_user_id_without_opening_transaction(): void
    {
        $userId = -1;

        $repository = Mockery::mock(IUserRepository::class);
        $idGenerator = $this->mockIdValidation($userId, false);

        $repository->shouldNotReceive('findById');
        $repository->shouldNotReceive('save');

        DB::shouldReceive('transaction')->never();

        $result = (new UpdatePasswordUseCase(
            $repository,
            $idGenerator
        ))->execute(
            new UpdatePasswordDTO(password: 'NewSecurePassword123!'),
            $userId
        );

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid user ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    public function test_it_fails_when_user_does_not_exist(): void
    {
        $userId = 123;

        $repository = Mockery::mock(IUserRepository::class);
        $idGenerator = $this->mockIdValidation($userId, true);

        $repository->shouldReceive('findById')
            ->once()
            ->with($userId)
            ->andReturn(null);

        $repository->shouldNotReceive('save');

        $this->mockTransaction();

        $result = (new UpdatePasswordUseCase(
            $repository,
            $idGenerator
        ))->execute(
            new UpdatePasswordDTO(password: 'NewSecurePassword123!'),
            $userId
        );

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'User not found.',
            $result->getError() ?? ''
        );
    }

    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $userId = 123;

        $repository = Mockery::mock(IUserRepository::class);
        $idGenerator = $this->mockIdValidation($userId, true);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'update users set password = ? where id = ?',
                    ['hashed-password', $userId],
                    new PDOException('Database failure.')
                )
            );

        $result = (new UpdatePasswordUseCase(
            $repository,
            $idGenerator
        ))->execute(
            new UpdatePasswordDTO(password: 'NewSecurePassword123!'),
            $userId
        );

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while updating the password.',
            $result->getError() ?? ''
        );
    }

    private function mockIdValidation(
        int $userId,
        bool $isValid,
    ): IdGeneratorInterface {
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with($userId)
            ->andReturn($isValid);

        return $idGenerator;
    }

    private function mockTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                static fn (Closure $callback, int $attempts) => $callback()
            );
    }
}