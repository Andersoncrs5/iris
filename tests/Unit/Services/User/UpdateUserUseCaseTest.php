<?php

namespace Tests\Unit\Services\User;

use App\DTOs\User\UpdateUserDTO;
use App\Services\User\UpdateUserUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class UpdateUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_updates_user_inside_transaction(): void
    {
        $id = 123;
        $dto = $this->makeDto();

        $user = new class {
            public string $name = 'Old name';
            public string $full_name = 'Old full name';
            public int $saveCalls = 0;

            public function save(): bool
            {
                $this->saveCalls++;

                return true;
            }
        };

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockUserLookup($id, $user);

        $result = (new UpdateUserUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'User update failed.'
        );

        $this->assertSame($user, $result->getValue());
        $this->assertSame($dto->name, $user->name);
        $this->assertSame($dto->fullName, $user->full_name);
        $this->assertSame(1, $user->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new UpdateUserUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid user ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_user_does_not_exist(): void
    {
        $id = 123;

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockUserLookup($id, null);

        $result = (new UpdateUserUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'User not found.',
            $result->getError() ?? ''
        );

        $this->assertSame(404, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $id = 123;

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $exception = new QueryException(
            'pgsql',
            'select * from users where id = ?',
            [$id],
            new PDOException('Database failure.')
        );

        $this->mockUserLookup($id, null, $exception);

        $result = (new UpdateUserUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while updating the user.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(): UpdateUserDTO
    {
        return new UpdateUserDTO(
            name: 'Updated name',
            fullName: 'Updated full name',
        );
    }

    private function mockIdValidation(
        int $id,
        bool $isValid,
    ): IdGeneratorInterface {
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with($id)
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

    private function mockUserLookup(
        int $id,
        mixed $user,
        ?QueryException $exception = null,
    ): void {
        $model = Mockery::mock('alias:App\Models\User');
        $query = Mockery::mock();

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $find = $query->shouldReceive('find')
            ->once()
            ->with($id);

        if ($exception !== null) {
            $find->andThrow($exception);
        } else {
            $find->andReturn($user);
        }
    }
}