<?php

namespace Tests\Feature\Services\PermissionUser;

use App\Services\PermissionUser\RestorePermissionUserUseCase;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class RestorePermissionUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_restores_permission_assignment(): void
    {
        $this->installModelAliases();

        $assignmentId = 50;

        $permissionUser = new FakeRestorePermissionUser(
            id: $assignmentId,
            trashed: true
        );

        $this->mockTransaction();

        $withTrashedQuery = Mockery::mock();
        $withTrashedQuery->shouldReceive('whereKey')
            ->once()
            ->with($assignmentId)
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('first')
            ->once()
            ->andReturn($permissionUser);

        FakeRestorePermissionUserBase::$withTrashedBuilder = $withTrashedQuery;

        $useCase = new RestorePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($permissionUser->restored);
        $this->assertSame($permissionUser, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_assignment_not_found(): void
    {
        $this->installModelAliases();

        $assignmentId = 999;

        $this->mockTransaction();

        $withTrashedQuery = Mockery::mock();
        $withTrashedQuery->shouldReceive('whereKey')
            ->once()
            ->with($assignmentId)
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('first')
            ->once()
            ->andReturn(null);

        FakeRestorePermissionUserBase::$withTrashedBuilder = $withTrashedQuery;

        $useCase = new RestorePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission assignment not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_assignment_is_not_deleted(): void
    {
        $this->installModelAliases();

        $assignmentId = 50;

        $permissionUser = new FakeRestorePermissionUser(
            id: $assignmentId,
            trashed: false
        );

        $this->mockTransaction();

        $withTrashedQuery = Mockery::mock();
        $withTrashedQuery->shouldReceive('whereKey')
            ->once()
            ->with($assignmentId)
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('first')
            ->once()
            ->andReturn($permissionUser);

        FakeRestorePermissionUserBase::$withTrashedBuilder = $withTrashedQuery;

        $useCase = new RestorePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('This permission assignment is not deleted.', $result->getError());
        $this->assertFalse($permissionUser->restored);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_handles_query_exception(): void
    {
        $this->installModelAliases();

        $assignmentId = 50;

        $this->mockTransaction();

        $pdoException = new PDOException('Database error');
        $queryException = new QueryException('mysql', 'select *', [], $pdoException);

        $withTrashedQuery = Mockery::mock();
        $withTrashedQuery->shouldReceive('whereKey')
            ->once()
            ->with($assignmentId)
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $withTrashedQuery->shouldReceive('first')
            ->once()
            ->andThrow($queryException);

        FakeRestorePermissionUserBase::$withTrashedBuilder = $withTrashedQuery;

        $useCase = new RestorePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected database error occurred while restoring the permission assignment.',
            $result->getError()
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeRestorePermissionUserBase::class,
            \App\Models\PermissionUserModel::class
        );
    }

    private function mockTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andReturnUsing(
                static fn (Closure $callback) => $callback()
            );
    }
}

/*
|--------------------------------------------------------------------------
| Modelos falsos usados somente nos testes
|--------------------------------------------------------------------------
*/

class FakeRestorePermissionUserBase
{
    public static mixed $withTrashedBuilder = null;

    public static function withTrashed(): mixed
    {
        return self::$withTrashedBuilder;
    }
}

final class FakeRestorePermissionUser extends FakeRestorePermissionUserBase
{
    public bool $restored = false;

    public function __construct(
        public readonly int $id,
        private readonly bool $trashed
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function trashed(): bool
    {
        return $this->trashed;
    }

    public function restore(): bool
    {
        $this->restored = true;
        return true;
    }
}