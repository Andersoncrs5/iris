<?php

namespace Tests\Feature\Services\PermissionUser;

use App\Services\PermissionUser\DeletePermissionUserUseCase;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class DeletePermissionUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        FakeDeleteUser::$queryBuilders = [];
        FakeDeleteUser::$queryIndex = 0;
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_removes_permission_from_user(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $assignmentId = 50;
        $permissionId = 100;

        $actor = new FakeDeleteUser(id: $actorId, isSuperAdmin: true);
        $targetUser = new FakeDeleteUser(id: $targetUserId, isSuperAdmin: false);
        $permission = new FakeDeletePermission(id: $permissionId, slug: 'reports.view');
        $permissionUser = new FakeDeletePermissionAssignment(id: $assignmentId, user_id: $targetUserId, permission: $permission);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, $targetUser);
        $this->mockPermissionUserLookup($assignmentId, $permissionUser);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($permissionUser->deleted);
        $this->assertContains('reports.view', $targetUser->removedPermissions);
        $this->assertSame($permissionUser, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_actor_is_not_authenticated(): void
    {
        $this->installModelAliases();

        $this->mockAuthUserId(null);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute(50);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You must be authenticated to remove a permission.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_actor_not_found(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, null, 2, null);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute(50);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Authenticated user not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_assignment_not_found(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $actor = new FakeDeleteUser(id: $actorId, isSuperAdmin: true);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        
        FakeDeleteUser::$queryBuilders = [$this->createActorQueryMock($actorId, $actor)];

        $this->mockPermissionUserLookup(999, null);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute(999);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission assignment not found or already deleted.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_target_user_not_found(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $assignmentId = 50;

        $actor = new FakeDeleteUser(id: $actorId, isSuperAdmin: true);
        $permissionUser = new FakeDeletePermissionAssignment(id: $assignmentId, user_id: $targetUserId, permission: null);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, null);
        $this->mockPermissionUserLookup($assignmentId, $permissionUser);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('The user associated with this permission no longer exists.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_not_found_on_assignment(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $assignmentId = 50;

        $actor = new FakeDeleteUser(id: $actorId, isSuperAdmin: true);
        $targetUser = new FakeDeleteUser(id: $targetUserId, isSuperAdmin: false);
        $permissionUser = new FakeDeletePermissionAssignment(id: $assignmentId, user_id: $targetUserId, permission: null);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, $targetUser);
        $this->mockPermissionUserLookup($assignmentId, $permissionUser);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('The permission associated with this assignment no longer exists.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_non_super_admin_removes_from_self(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $assignmentId = 50;
        $permissionId = 100;

        $actor = new FakeDeleteUser(id: $actorId, isSuperAdmin: false);
        $permission = new FakeDeletePermission(id: $permissionId, slug: 'reports.view');
        $permissionUser = new FakeDeletePermissionAssignment(id: $assignmentId, user_id: $actorId, permission: $permission);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $actorId, $actor);
        $this->mockPermissionUserLookup($assignmentId, $permissionUser);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You cannot remove permissions from your own account.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_non_super_admin_removes_privileged_permission(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $assignmentId = 50;
        $permissionId = 100;

        $actor = new FakeDeleteUser(id: $actorId, isSuperAdmin: false);
        $targetUser = new FakeDeleteUser(id: $targetUserId, isSuperAdmin: false);
        $permission = new FakeDeletePermission(id: $permissionId, slug: 'super.adm');
        $permissionUser = new FakeDeletePermissionAssignment(id: $assignmentId, user_id: $targetUserId, permission: $permission);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, $targetUser);
        $this->mockPermissionUserLookup($assignmentId, $permissionUser);

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Only a Super Admin can remove administrative permissions.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_handles_query_exception(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $assignmentId = 50;

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();

        $pdoException = new PDOException('DB Error');
        $queryException = new QueryException('mysql', 'select *', [], $pdoException);

        $actorQuery = Mockery::mock();
        $actorQuery->shouldReceive('whereKey')->once()->with($actorId)->andReturnSelf();
        $actorQuery->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $actorQuery->shouldReceive('first')->once()->andThrow($queryException);

        FakeDeleteUser::$queryBuilders = [$actorQuery];

        $useCase = new DeletePermissionUserUseCase();
        $result = $useCase->execute($assignmentId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('An unexpected database error occurred while removing the permission.', $result->getError());
    }

    private function installModelAliases(): void
    {
        class_alias(FakeDeleteUser::class, \App\Models\User::class);
        class_alias(FakeDeletePermissionModelBase::class, \App\Models\PermissionModel::class);
        class_alias(FakeDeletePermissionAssignmentBase::class, \App\Models\PermissionUserModel::class);
    }

    private function mockAuthUserId(?int $id): void
    {
        Auth::shouldReceive('id')->andReturn($id);
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

    private function createActorQueryMock(int $actorId, ?FakeDeleteUser $actor): mixed
    {
        $query = Mockery::mock();
        $query->shouldReceive('whereKey')->once()->with($actorId)->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn($actor);
        return $query;
    }

    private function mockUserLookups(int $actorId, ?FakeDeleteUser $actor, int $targetUserId, ?FakeDeleteUser $targetUser): void
    {
        $actorQuery = $this->createActorQueryMock($actorId, $actor);

        if ($actor !== null) {
            $targetQuery = Mockery::mock();
            $targetQuery->shouldReceive('whereKey')->once()->with($targetUserId)->andReturnSelf();
            $targetQuery->shouldReceive('lockForUpdate')->once()->andReturnSelf();
            $targetQuery->shouldReceive('first')->once()->andReturn($targetUser);

            FakeDeleteUser::$queryBuilders = [$actorQuery, $targetQuery];
        } else {
            FakeDeleteUser::$queryBuilders = [$actorQuery];
        }
    }

    private function mockPermissionUserLookup(int $assignmentId, ?FakeDeletePermissionAssignment $permissionUser): void
    {
        $query = Mockery::mock();
        $query->shouldReceive('whereKey')->once()->with($assignmentId)->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn($permissionUser);

        FakeDeletePermissionAssignmentBase::$queryBuilder = $query;
    }
}

/*
|--------------------------------------------------------------------------
| Modelos falsos usados somente nos testes
|--------------------------------------------------------------------------
*/

class FakeDeleteUser
{
    public static array $queryBuilders = [];
    public static int $queryIndex = 0;
    public array $removedPermissions = [];

    public function __construct(
        public readonly int $id,
        private readonly bool $isSuperAdmin = false
    ) {}

    public static function query(): mixed
    {
        $builder = self::$queryBuilders[self::$queryIndex] ?? null;
        self::$queryIndex++;
        return $builder;
    }

    public function getKey(): int
    {
        return $this->id;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    public function hasPermissionTo(string $permission): bool
    {
        return false;
    }

    public function removePermission(string $slug): void
    {
        $this->removedPermissions[] = $slug;
    }
}

class FakeDeletePermissionModelBase
{
    public static function query(): mixed
    {
        return null;
    }
}

final class FakeDeletePermission extends FakeDeletePermissionModelBase
{
    public function __construct(
        public readonly int $id,
        public string $slug
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }
}

class FakeDeletePermissionAssignmentBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeDeletePermissionAssignment extends FakeDeletePermissionAssignmentBase
{
    public bool $deleted = false;

    public function __construct(
        public readonly int $id,
        public readonly int $user_id,
        public ?FakeDeletePermission $permission
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