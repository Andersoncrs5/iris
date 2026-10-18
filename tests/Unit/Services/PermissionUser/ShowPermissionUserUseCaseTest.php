<?php

namespace Tests\Feature\Services\PermissionUser;

use App\Services\PermissionUser\ShowPermissionUserUseCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class ShowPermissionUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_finds_permission_assignment(): void
    {
        $this->installModelAliases();

        $assignmentId = 50;
        $permissionUser = new FakeShowPermissionUser(id: $assignmentId);

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('with')
            ->once()
            ->with(['user', 'permission'])
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($assignmentId)
            ->andReturn($permissionUser);

        FakeShowPermissionUserBase::$queryBuilder = $queryMock;

        $useCase = new ShowPermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertNotNull($result);
        $this->assertSame($permissionUser, $result);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_null_when_permission_assignment_not_found(): void
    {
        $this->installModelAliases();

        $assignmentId = 999;

        $queryMock = Mockery::mock();
        $queryMock->shouldReceive('with')
            ->once()
            ->with(['user', 'permission'])
            ->andReturnSelf();

        $queryMock->shouldReceive('find')
            ->once()
            ->with($assignmentId)
            ->andReturn(null);

        FakeShowPermissionUserBase::$queryBuilder = $queryMock;

        $useCase = new ShowPermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertNull($result);
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeShowPermissionUserBase::class,
            \App\Models\PermissionUserModel::class
        );
    }
}

/*
|--------------------------------------------------------------------------
| Modelos falsos usados somente nos testes
|--------------------------------------------------------------------------
*/

class FakeShowPermissionUserBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeShowPermissionUser extends FakeShowPermissionUserBase
{
    public function __construct(
        public readonly int $id
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }
}