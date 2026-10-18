<?php

namespace Tests\Feature\Services\PermissionUser;

use App\DTOs\PermissionUser\CreatePermissionUserDTO;
use App\Services\PermissionUser\CreatePermissionUserUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
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

class CreatePermissionUserUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_assigns_permission_to_user(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $permissionId = 100;
        $generatedId = 999;

        $dto = new CreatePermissionUserDTO(
            userId: $targetUserId,
            permissionId: $permissionId
        );

        $actor = new FakeAssignUser(id: $actorId, isSuperAdmin: true);
        $targetUser = new FakeAssignUser(id: $targetUserId, isSuperAdmin: false);
        $permission = new FakeAssignPermission(id: $permissionId, slug: 'reports.view');
        $createdPivot = new FakeAssignPermissionUser(id: $generatedId);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, $targetUser);
        $this->mockPermissionLookup($permissionId, $permission);

        // Mock ID Generator
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn($generatedId);

        // Mock PermissionUserModel::withTrashed()->where()->where()->lockForUpdate()->first()
        $withTrashedQuery = Mockery::mock();
        $withTrashedQuery->shouldReceive('where')->once()->with('user_id', $targetUserId)->andReturnSelf();
        $withTrashedQuery->shouldReceive('where')->once()->with('permission_id', $permissionId)->andReturnSelf();
        $withTrashedQuery->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $withTrashedQuery->shouldReceive('first')->once()->andReturn(null);

        FakeAssignPermissionUserBase::$withTrashedBuilder = $withTrashedQuery;

        // Mock PermissionUserModel::query()->create()
        $pivotQuery = Mockery::mock();
        $pivotQuery->shouldReceive('create')
            ->once()
            ->with([
                'id' => $generatedId,
                'user_id' => $targetUserId,
                'permission_id' => $permissionId,
                'version' => 1,
            ])
            ->andReturn($createdPivot);

        FakeAssignPermissionUserBase::$queryBuilder = $pivotQuery;

        $useCase = new CreatePermissionUserUseCase($idGenerator);
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($createdPivot, $result->getValue());
        $this->assertContains('reports.view', $targetUser->permissions);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_actor_is_not_authenticated(): void
    {
        $this->installModelAliases();

        $dto = new CreatePermissionUserDTO(userId: 2, permissionId: 100);
        $this->mockAuthUserId(null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $useCase = new CreatePermissionUserUseCase($idGenerator);
        
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You must be authenticated to assign a permission.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_actor_not_found(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $dto = new CreatePermissionUserDTO(userId: 2, permissionId: 100);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, null, 2, null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $useCase = new CreatePermissionUserUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Authenticated user not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_target_user_not_found(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $dto = new CreatePermissionUserDTO(userId: $targetUserId, permissionId: 100);
        $actor = new FakeAssignUser(id: $actorId, isSuperAdmin: true);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $useCase = new CreatePermissionUserUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('The selected user does not exist or has been deleted.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_not_found(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $permissionId = 100;
        $dto = new CreatePermissionUserDTO(userId: $targetUserId, permissionId: $permissionId);

        $actor = new FakeAssignUser(id: $actorId, isSuperAdmin: true);
        $targetUser = new FakeAssignUser(id: $targetUserId, isSuperAdmin: false);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, $targetUser);
        $this->mockPermissionLookup($permissionId, null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $useCase = new CreatePermissionUserUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('The selected permission does not exist or has been deleted.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_non_super_admin_assigns_to_self(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $dto = new CreatePermissionUserDTO(userId: 1, permissionId: 100);

        $actor = new FakeAssignUser(id: $actorId, isSuperAdmin: false);
        $targetUser = $actor;
        $permission = new FakeAssignPermission(id: 100, slug: 'reports.view');

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, 1, $targetUser);
        $this->mockPermissionLookup(100, $permission);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $useCase = new CreatePermissionUserUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You cannot assign permissions to your own account. Only a Super Admin can do this.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_non_super_admin_assigns_privileged_permission(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $dto = new CreatePermissionUserDTO(userId: $targetUserId, permissionId: 100);

        $actor = new FakeAssignUser(id: $actorId, isSuperAdmin: false);
        $targetUser = new FakeAssignUser(id: $targetUserId, isSuperAdmin: false);
        $permission = new FakeAssignPermission(id: 100, slug: 'super.adm');

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, $targetUser);
        $this->mockPermissionLookup(100, $permission);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $useCase = new CreatePermissionUserUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Only a Super Admin can grant administrative permissions.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_assignment_already_exists(): void
    {
        $this->installModelAliases();

        $actorId = 1;
        $targetUserId = 2;
        $permissionId = 100;
        $dto = new CreatePermissionUserDTO(userId: $targetUserId, permissionId: $permissionId);

        $actor = new FakeAssignUser(id: $actorId, isSuperAdmin: true);
        $targetUser = new FakeAssignUser(id: $targetUserId, isSuperAdmin: false);
        $permission = new FakeAssignPermission(id: $permissionId, slug: 'reports.view');
        $existingAssignment = new FakeAssignPermissionUser(id: 50, trashed: false);

        $this->mockAuthUserId($actorId);
        $this->mockTransaction();
        $this->mockUserLookups($actorId, $actor, $targetUserId, $targetUser);
        $this->mockPermissionLookup($permissionId, $permission);

        $withTrashedQuery = Mockery::mock();
        $withTrashedQuery->shouldReceive('where')->once()->with('user_id', $targetUserId)->andReturnSelf();
        $withTrashedQuery->shouldReceive('where')->once()->with('permission_id', $permissionId)->andReturnSelf();
        $withTrashedQuery->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $withTrashedQuery->shouldReceive('first')->once()->andReturn($existingAssignment);

        FakeAssignPermissionUserBase::$withTrashedBuilder = $withTrashedQuery;

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $useCase = new CreatePermissionUserUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('This user already has this permission.', $result->getError());
    }

    private function installModelAliases(): void
    {
        class_alias(FakeAssignUser::class, \App\Models\User::class);
        class_alias(FakeAssignPermissionModelBase::class, \App\Models\PermissionModel::class);
        class_alias(FakeAssignPermissionUserBase::class, \App\Models\PermissionUserModel::class);
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

    private function mockUserLookups(int $actorId, ?FakeAssignUser $actor, int $targetUserId, ?FakeAssignUser $targetUser): void
    {
        $query = Mockery::mock();
        
        // 1ª Chamada: Busca do ator ($actor)
        $query->shouldReceive('whereKey')->once()->with($actorId)->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn($actor);

        // Se o ator for encontrado, a 2ª chamada ocorre (busca do usuário alvo)
        if ($actor !== null) {
            $query->shouldReceive('whereKey')->once()->with($targetUserId)->andReturnSelf();
            $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
            $query->shouldReceive('first')->once()->andReturn($targetUser);
        }

        FakeAssignUser::$queryBuilder = $query;
    }

    private function mockPermissionLookup(int $permissionId, ?FakeAssignPermission $permission): void
    {
        $query = Mockery::mock();
        $query->shouldReceive('whereKey')->once()->with($permissionId)->andReturnSelf();
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $query->shouldReceive('first')->once()->andReturn($permission);

        FakeAssignPermissionModelBase::$queryBuilder = $query;
    }
}

/*
|--------------------------------------------------------------------------
| Modelos falsos usados somente nos testes
|--------------------------------------------------------------------------
*/

class FakeAssignUser
{
    public static mixed $queryBuilder = null;
    public array $permissions = [];

    public function __construct(
        public readonly int $id,
        private readonly bool $isSuperAdmin = false
    ) {}

    public static function query(): mixed
    {
        return self::$queryBuilder;
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

    public function addPermission(string $slug): void
    {
        $this->permissions[] = $slug;
    }
}

class FakeAssignPermissionModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeAssignPermission extends FakeAssignPermissionModelBase
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

class FakeAssignPermissionUserBase
{
    public static mixed $queryBuilder = null;
    public static mixed $withTrashedBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }

    public static function withTrashed(): mixed
    {
        return self::$withTrashedBuilder;
    }
}

final class FakeAssignPermissionUser extends FakeAssignPermissionUserBase
{
    public function __construct(
        public readonly int $id,
        private readonly bool $trashed = false
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function trashed(): bool
    {
        return $this->trashed;
    }

    public function load(mixed $relations): self
    {
        return $this;
    }
}