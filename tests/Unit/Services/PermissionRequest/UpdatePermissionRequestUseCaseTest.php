<?php

namespace Tests\Feature\Services\PermissionRequest;

use App\DTOs\PermissionRequest\UpdatePermissionRequestDTO;
use App\Services\PermissionRequest\UpdatePermissionRequestUseCase;
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

class UpdatePermissionRequestUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_updates_pending_permission_request(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $userId = 10;

        $dto = new UpdatePermissionRequestDTO(
            permissionId: 5,
            reason: 'Updated reason for accessing permissions.'
        );

        $permissionRequest = new FakeUpdateRequestModel(
            id: $requestId,
            userId: $userId,
            status: PermissionRequestStatus::PENDING->value,
            permission_id: 2,
            reason: 'Original reason'
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

        FakeUpdateRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new UpdatePermissionRequestUseCase();
        $result = $useCase->execute($dto, $requestId, $userId);

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($permissionRequest->saved);
        $this->assertSame(5, $permissionRequest->permission_id);
        $this->assertSame('Updated reason for accessing permissions.', $permissionRequest->reason);
        $this->assertSame($permissionRequest, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_request_not_found(): void
    {
        $this->installModelAliases();

        $requestId = 999;
        $userId = 10;

        $dto = new UpdatePermissionRequestDTO(
            permissionId: 5,
            reason: 'Updated reason'
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
            ->andReturn(null);

        FakeUpdateRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new UpdatePermissionRequestUseCase();
        $result = $useCase->execute($dto, $requestId, $userId);

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

        $dto = new UpdatePermissionRequestDTO(
            permissionId: 5,
            reason: 'Updated reason'
        );

        $permissionRequest = new FakeUpdateRequestModel(
            id: $requestId,
            userId: $userId,
            status: PermissionRequestStatus::APPROVED->value,
            permission_id: 2,
            reason: 'Original reason'
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

        FakeUpdateRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new UpdatePermissionRequestUseCase();
        $result = $useCase->execute($dto, $requestId, $userId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Only pending requests can be updated.', $result->getError());
        $this->assertFalse($permissionRequest->saved);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_handles_query_exception(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $userId = 10;

        $dto = new UpdatePermissionRequestDTO(
            permissionId: 5,
            reason: 'Updated reason'
        );

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

        FakeUpdateRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new UpdatePermissionRequestUseCase();
        $result = $useCase->execute($dto, $requestId, $userId);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected database error occurred while updating the permission request.',
            $result->getError()
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeUpdateRequestModelBase::class,
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

class FakeUpdateRequestModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeUpdateRequestModel extends FakeUpdateRequestModelBase
{
    public bool $saved = false;

    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public string $status,
        public int $permission_id,
        public ?string $reason = null
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

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        return $this;
    }

    public function save(array $options = []): bool
    {
        $this->saved = true;
        return true;
    }
}