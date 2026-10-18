<?php

namespace Tests\Feature\Services\PermissionRequest;

use App\Services\PermissionRequest\DeletePermissionRequestUseCase;
use App\Utils\Enums\PermissionRequestStatus\PermissionRequestStatus;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class DeletePermissionRequestUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_deletes_pending_permission_request(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $userId = 10;

        $permissionRequest = new FakeDeleteRequestModel(
            id: $requestId,
            userId: $userId,
            status: PermissionRequestStatus::PENDING->value
        );

        $this->mockTransaction();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->once()
            ->with('user_id', $userId)
            ->andReturnSelf();

        $queryMock->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($requestId)
            ->andReturn($permissionRequest);

        FakeDeleteRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new DeletePermissionRequestUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($permissionRequest->deleted);
        $this->assertSame($permissionRequest, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_request_not_found(): void
    {
        $this->installModelAliases();

        $requestId = 999;
        $userId = 10;

        $this->mockTransaction();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->once()
            ->with('user_id', $userId)
            ->andReturnSelf();

        $queryMock->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($requestId)
            ->andReturn(null);

        FakeDeleteRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new DeletePermissionRequestUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission request not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_request_is_not_pending(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $userId = 10;

        $permissionRequest = new FakeDeleteRequestModel(
            id: $requestId,
            userId: $userId,
            status: PermissionRequestStatus::APPROVED->value
        );

        $this->mockTransaction();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->once()
            ->with('user_id', $userId)
            ->andReturnSelf();

        $queryMock->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($requestId)
            ->andReturn($permissionRequest);

        FakeDeleteRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new DeletePermissionRequestUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Only pending requests can be deleted.', $result->getError());
        $this->assertFalse($permissionRequest->deleted);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_handles_query_exception(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $userId = 10;

        $this->mockTransaction();

        $pdoException = new PDOException('Database error');
        $queryException = new QueryException('mysql', 'select *', [], $pdoException);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('where')
            ->once()
            ->with('user_id', $userId)
            ->andReturnSelf();

        $queryMock->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($requestId)
            ->andThrow($queryException);

        FakeDeleteRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new DeletePermissionRequestUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected database error occurred while deleting the permission request.',
            $result->getError()
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeDeleteRequestModelBase::class,
            \App\Models\PermissionRequestModel::class
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

class FakeDeleteRequestModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeDeleteRequestModel extends FakeDeleteRequestModelBase
{
    public bool $deleted = false;

    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public string $status
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function getRawOriginal(string $key): mixed
    {
        if ($key === 'status') {
            return $this->status;
        }

        return null;
    }

    public function delete(): bool
    {
        $this->deleted = true;
        return true;
    }
}