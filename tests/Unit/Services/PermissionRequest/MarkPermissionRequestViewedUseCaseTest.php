<?php

namespace Tests\Feature\Services\PermissionRequest;

use App\Services\PermissionRequest\MarkPermissionRequestViewedUseCase;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class MarkPermissionRequestViewedUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_marks_permission_request_as_viewed(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $userId = 10;

        $permissionRequest = new FakeMarkViewedRequestModel(
            id: $requestId,
            userId: $userId,
            viewed_at: null
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

        FakeMarkViewedRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new MarkPermissionRequestViewedUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isSuccess());
        $this->assertNotNull($permissionRequest->viewed_at);
        $this->assertTrue($permissionRequest->saved);
        $this->assertSame($permissionRequest, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_ok_when_already_viewed(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $userId = 10;
        $existingViewedAt = now()->subHour();

        $permissionRequest = new FakeMarkViewedRequestModel(
            id: $requestId,
            userId: $userId,
            viewed_at: $existingViewedAt
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

        FakeMarkViewedRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new MarkPermissionRequestViewedUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($existingViewedAt, $permissionRequest->viewed_at);
        $this->assertFalse($permissionRequest->saved); // Não deve chamar save() se já foi visto
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

        FakeMarkViewedRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new MarkPermissionRequestViewedUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission request not found.', $result->getError());
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

        FakeMarkViewedRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new MarkPermissionRequestViewedUseCase();
        $result = $useCase->execute($requestId, $userId);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected database error occurred while marking the permission request as viewed.',
            $result->getError()
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeMarkViewedRequestModelBase::class,
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

class FakeMarkViewedRequestModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeMarkViewedRequestModel extends FakeMarkViewedRequestModelBase
{
    public bool $saved = false;

    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public mixed $viewed_at
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function save(array $options = []): bool
    {
        $this->saved = true;
        return true;
    }
}