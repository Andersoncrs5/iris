<?php

namespace Tests\Feature\Services\PermissionRequest;

use App\DTOs\PermissionRequest\ChangePermissionRequestStatusDTO;
use App\Services\PermissionRequest\ChangePermissionRequestStatusUseCase;
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

class ChangePermissionRequestStatusUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_reviews_permission_request(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $reviewerId = 50;
        $comment = 'Approved by manager.';

        $dto = new ChangePermissionRequestStatusDTO(
            status: PermissionRequestStatus::APPROVED,
            reviewComment: $comment
        );

        $permissionRequest = new FakeChangePermissionRequestModel(
            id: $requestId,
            status: PermissionRequestStatus::PENDING->value
        );

        $this->mockTransaction();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($requestId)
            ->andReturn($permissionRequest);

        FakeChangePermissionRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new ChangePermissionRequestStatusUseCase();
        $result = $useCase->execute($dto, $requestId, $reviewerId);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($permissionRequest, $result->getValue());
        $this->assertSame(PermissionRequestStatus::APPROVED->value, $permissionRequest->status);
        $this->assertSame($reviewerId, $permissionRequest->reviewed_by);
        $this->assertNotNull($permissionRequest->reviewed_at);
        $this->assertSame($comment, $permissionRequest->review_comment);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_request_is_not_found(): void
    {
        $this->installModelAliases();

        $requestId = 999;
        $reviewerId = 50;

        $dto = new ChangePermissionRequestStatusDTO(
            status: PermissionRequestStatus::APPROVED,
            reviewComment: 'Approved'
        );

        $this->mockTransaction();

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($requestId)
            ->andReturn(null);

        FakeChangePermissionRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new ChangePermissionRequestStatusUseCase();
        $result = $useCase->execute($dto, $requestId, $reviewerId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission request not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_handles_query_exception_properly(): void
    {
        $this->installModelAliases();

        $requestId = 100;
        $reviewerId = 50;

        $dto = new ChangePermissionRequestStatusDTO(
            status: PermissionRequestStatus::REJECTED,
            reviewComment: 'Denied'
        );

        $this->mockTransaction();

        $pdoException = new PDOException('Database failure');
        $queryException = new QueryException('mysql', 'select *', [], $pdoException);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('lockForUpdate')
            ->once()
            ->andThrow($queryException);

        FakeChangePermissionRequestModelBase::$queryBuilder = $queryMock;

        $useCase = new ChangePermissionRequestStatusUseCase();
        $result = $useCase->execute($dto, $requestId, $reviewerId);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected database error occurred while updating the permission request status.',
            $result->getError()
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeChangePermissionRequestModelBase::class,
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

class FakeChangePermissionRequestModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeChangePermissionRequestModel extends FakeChangePermissionRequestModelBase
{
    public function __construct(
        private readonly int $id,
        public string $status,
        public ?int $reviewed_by = null,
        public mixed $reviewed_at = null,
        public ?string $review_comment = null,
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function save(array $options = []): bool
    {
        return true;
    }
}