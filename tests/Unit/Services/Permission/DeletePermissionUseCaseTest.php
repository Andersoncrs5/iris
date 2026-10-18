<?php

namespace Tests\Feature\Services\Permission;

use App\Services\Pemission\DeletePermissionUseCase;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class DeletePermissionUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_deletes_permission_and_removes_from_users(): void
    {
        $this->installModelAliases();

        $permissionId = 100;
        $permissionSlug = 'reports.view';

        $permission = new FakeDeletePermissionModel(
            id: $permissionId,
            module: 'reports',
            slug: $permissionSlug
        );

        $user = new FakeDeletePermissionUser(501);

        $this->mockGateAuthorized('permission.delete');
        $this->mockTransaction();

        // Mocking Permission Query
        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('whereKey')
            ->once()
            ->with($permissionId)
            ->andReturnSelf();

        $permissionQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $permissionQuery->shouldReceive('first')
            ->once()
            ->andReturn($permission);

        FakeDeletePermissionModelBase::$queryBuilder = $permissionQuery;

        // Mocking User Query for chunkById
        $userQuery = Mockery::mock();
        $userQuery->shouldReceive('whereJsonContains')
            ->once()
            ->with('permissions', $permissionSlug)
            ->andReturnSelf();

        $userQuery->shouldReceive('chunkById')
            ->once()
            ->with(100, Mockery::type(Closure::class))
            ->andReturnUsing(
                function (int $count, Closure $callback) use ($user) {
                    $usersCollection = new EloquentCollection([$user]);
                    $callback($usersCollection);
                    return true;
                }
            );

        FakeDeletePermissionUserBase::$queryBuilder = $userQuery;

        $useCase = new DeletePermissionUseCase();
        $result = $useCase->execute($permissionId);

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($permission->deleted);
        $this->assertContains($permissionSlug, $user->removedPermissions);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_not_found(): void
    {
        $this->installModelAliases();

        $permissionId = 999;

        $this->mockGateAuthorized('permission.delete');
        $this->mockTransaction();

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('whereKey')
            ->once()
            ->with($permissionId)
            ->andReturnSelf();

        $permissionQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $permissionQuery->shouldReceive('first')
            ->once()
            ->andReturn(null);

        FakeDeletePermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new DeletePermissionUseCase();
        $result = $useCase->execute($permissionId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_belongs_to_system_module(): void
    {
        $this->installModelAliases();

        $permissionId = 1;

        $permission = new FakeDeletePermissionModel(
            id: $permissionId,
            module: '  SYSTEM  ', // Testando trim e case-insensitive
            slug: 'system.manage'
        );

        $this->mockGateAuthorized('permission.delete');
        $this->mockTransaction();

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('whereKey')
            ->once()
            ->with($permissionId)
            ->andReturnSelf();

        $permissionQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $permissionQuery->shouldReceive('first')
            ->once()
            ->andReturn($permission);

        FakeDeletePermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new DeletePermissionUseCase();
        $result = $useCase->execute($permissionId);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'Permissions belonging to the system module cannot be deleted.',
            $result->getError()
        );
        $this->assertFalse($permission->deleted);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_handles_unexpected_exceptions(): void
    {
        $this->installModelAliases();

        $permissionId = 100;

        $this->mockGateAuthorized('permission.delete');
        $this->mockTransaction();

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('whereKey')
            ->once()
            ->with($permissionId)
            ->andThrow(new Exception('Database failure'));

        FakeDeletePermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new DeletePermissionUseCase();
        $result = $useCase->execute($permissionId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Failed to delete permission.', $result->getError());
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeDeletePermissionModelBase::class,
            \App\Models\PermissionModel::class
        );

        class_alias(
            FakeDeletePermissionUserBase::class,
            \App\Models\User::class
        );
    }

    private function mockGateAuthorized(string $ability): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with($ability)
            ->andReturnTrue();
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

class FakeDeletePermissionModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeDeletePermissionModel extends FakeDeletePermissionModelBase
{
    public bool $deleted = false;

    public function __construct(
        private readonly int $id,
        public string $module,
        public string $slug,
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function delete(): bool
    {
        $this->deleted = true;
        return true;
    }
}

class FakeDeletePermissionUserBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeDeletePermissionUser extends FakeDeletePermissionUserBase
{
    public array $removedPermissions = [];

    public function __construct(
        private readonly int $id
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function removePermission(string $slug): void
    {
        $this->removedPermissions[] = $slug;
    }
}