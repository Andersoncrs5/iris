<?php

namespace Tests\Feature\Services\PermissionRequest;

use App\DTOs\PermissionRequest\CreatePermissionRequestDTO;
use App\Services\PermissionRequest\CreatePermissionRequestUseCase;
use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class CreatePermissionRequestUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_creates_permission_request(): void
    {
        $this->installModelAliases();

        $userId = 10;
        $permissionId = 100;
        $slug = 'reports.view';

        $dto = new CreatePermissionRequestDTO(
            permissionId: $permissionId,
            reason: 'Need access for auditing.'
        );

        $user = new FakeCreateRequestUser(
            id: $userId,
            isSuperAdmin: false,
            permissions: ['dashboard.view']
        );

        $permission = new FakeCreateRequestPermission(
            id: $permissionId,
            slug: $slug
        );

        $createdRequest = new FakeCreateRequestPermissionRequest(
            id: 1,
            userId: $userId,
            permissionId: $permissionId
        );

        $this->mockGateAuthorized('permission.request.create');
        $this->mockAuthUser($user, $userId);
        $this->mockTransaction();

        // 1. Mock PermissionModel::find
        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('find')
            ->once()
            ->with($permissionId)
            ->andReturn($permission);

        FakeCreateRequestPermissionModelBase::$queryBuilder = $permissionQuery;

        // 2. Mock PermissionUserModel::exists
        $permissionUserQuery = Mockery::mock();
        $permissionUserQuery->shouldReceive('where')
            ->once()
            ->with('user_id', $userId)
            ->andReturnSelf();

        $permissionUserQuery->shouldReceive('where')
            ->once()
            ->with('permission_id', $permissionId)
            ->andReturnSelf();

        $permissionUserQuery->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        FakeCreateRequestPermissionUserModelBase::$queryBuilder = $permissionUserQuery;

        // 3. Mock PermissionRequestModel::create
        $requestQuery = Mockery::mock();
        $requestQuery->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $userId,
                'permission_id' => $permissionId,
                'reason' => 'Need access for auditing.',
            ])
            ->andReturn($createdRequest);

        FakeCreateRequestPermissionRequestModelBase::$queryBuilder = $requestQuery;

        $useCase = new CreatePermissionRequestUseCase();
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($createdRequest, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_user_is_not_authenticated(): void
    {
        $this->installModelAliases();

        $dto = new CreatePermissionRequestDTO(permissionId: 100, reason: 'Test');

        $this->mockGateAuthorized('permission.request.create');
        $this->mockAuthUser(null, null);

        $useCase = new CreatePermissionRequestUseCase();
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You must be authenticated to create a permission request.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_not_found(): void
    {
        $this->installModelAliases();

        $userId = 10;
        $permissionId = 999;

        $dto = new CreatePermissionRequestDTO(permissionId: $permissionId, reason: 'Test');
        $user = new FakeCreateRequestUser(id: $userId, isSuperAdmin: false);

        $this->mockGateAuthorized('permission.request.create');
        $this->mockAuthUser($user, $userId);
        $this->mockTransaction();

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('find')
            ->once()
            ->with($permissionId)
            ->andReturn(null);

        FakeCreateRequestPermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new CreatePermissionRequestUseCase();
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_user_is_super_admin(): void
    {
        $this->installModelAliases();

        $userId = 10;
        $permissionId = 100;

        $dto = new CreatePermissionRequestDTO(permissionId: $permissionId, reason: 'Test');
        $user = new FakeCreateRequestUser(id: $userId, isSuperAdmin: true);
        $permission = new FakeCreateRequestPermission(id: $permissionId, slug: 'reports.view');

        $this->mockGateAuthorized('permission.request.create');
        $this->mockAuthUser($user, $userId);
        $this->mockTransaction();

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('find')
            ->once()
            ->with($permissionId)
            ->andReturn($permission);

        FakeCreateRequestPermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new CreatePermissionRequestUseCase();
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You already have access to this permission. No request is necessary.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_user_already_has_permission_in_json(): void
    {
        $this->installModelAliases();

        $userId = 10;
        $permissionId = 100;
        $slug = 'reports.view';

        $dto = new CreatePermissionRequestDTO(permissionId: $permissionId, reason: 'Test');
        $user = new FakeCreateRequestUser(id: $userId, isSuperAdmin: false, permissions: [$slug]);
        $permission = new FakeCreateRequestPermission(id: $permissionId, slug: $slug);

        $this->mockGateAuthorized('permission.request.create');
        $this->mockAuthUser($user, $userId);
        $this->mockTransaction();

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('find')
            ->once()
            ->with($permissionId)
            ->andReturn($permission);

        FakeCreateRequestPermissionModelBase::$queryBuilder = $permissionQuery;

        // Mock opcional/preventivo caso a avaliação mude de ordem futuramente
        $permissionUserQuery = Mockery::mock();
        $permissionUserQuery->shouldReceive('where')->andReturnSelf();
        $permissionUserQuery->shouldReceive('exists')->andReturn(false);
        FakeCreateRequestPermissionUserModelBase::$queryBuilder = $permissionUserQuery;

        $useCase = new CreatePermissionRequestUseCase();
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You already have this permission. No request is necessary.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_user_already_has_permission_in_pivot(): void
    {
        $this->installModelAliases();

        $userId = 10;
        $permissionId = 100;
        $slug = 'reports.view';

        $dto = new CreatePermissionRequestDTO(permissionId: $permissionId, reason: 'Test');
        $user = new FakeCreateRequestUser(id: $userId, isSuperAdmin: false, permissions: []);
        $permission = new FakeCreateRequestPermission(id: $permissionId, slug: $slug);

        $this->mockGateAuthorized('permission.request.create');
        $this->mockAuthUser($user, $userId);
        $this->mockTransaction();

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('find')
            ->once()
            ->with($permissionId)
            ->andReturn($permission);

        FakeCreateRequestPermissionModelBase::$queryBuilder = $permissionQuery;

        $permissionUserQuery = Mockery::mock();
        $permissionUserQuery->shouldReceive('where')
            ->once()
            ->with('user_id', $userId)
            ->andReturnSelf();

        $permissionUserQuery->shouldReceive('where')
            ->once()
            ->with('permission_id', $permissionId)
            ->andReturnSelf();

        $permissionUserQuery->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        FakeCreateRequestPermissionUserModelBase::$queryBuilder = $permissionUserQuery;

        $useCase = new CreatePermissionRequestUseCase();
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('You already have this permission. No request is necessary.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_handles_query_exception(): void
    {
        $this->installModelAliases();

        $userId = 10;
        $permissionId = 100;

        $dto = new CreatePermissionRequestDTO(permissionId: $permissionId, reason: 'Test');
        $user = new FakeCreateRequestUser(id: $userId, isSuperAdmin: false);

        $this->mockGateAuthorized('permission.request.create');
        $this->mockAuthUser($user, $userId);
        $this->mockTransaction();

        $pdoException = new PDOException('DB Error');
        $queryException = new QueryException('mysql', 'select *', [], $pdoException);

        $permissionQuery = Mockery::mock();
        $permissionQuery->shouldReceive('find')
            ->once()
            ->with($permissionId)
            ->andThrow($queryException);

        FakeCreateRequestPermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new CreatePermissionRequestUseCase();
        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());
        $this->assertSame('An unexpected database error occurred while creating the permission request.', $result->getError());
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeCreateRequestUser::class,
            \App\Models\User::class
        );

        class_alias(
            FakeCreateRequestPermissionModelBase::class,
            \App\Models\PermissionModel::class
        );

        class_alias(
            FakeCreateRequestPermissionUserModelBase::class,
            \App\Models\PermissionUserModel::class
        );

        class_alias(
            FakeCreateRequestPermissionRequestModelBase::class,
            \App\Models\PermissionRequestModel::class
        );
    }

    private function mockGateAuthorized(string $ability): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with($ability)
            ->andReturnTrue();
    }

    private function mockAuthUser(?object $user, ?int $id): void
    {
        if ($user !== null) {
            $this->app['auth']->setUser($user);
        } else {
            $this->app['auth']->logout();
        }
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

class FakeCreateRequestUser implements Authenticatable
{
    public function __construct(
        public readonly ?int $id,
        private readonly bool $isSuperAdmin = false,
        public array $permissions = []
    ) {}

    public function getKey(): ?int
    {
        return $this->id;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdmin;
    }

    public function getAuthIdentifierName(): string { return 'id'; }
    public function getAuthIdentifier(): ?int { return $this->id; }
    public function getAuthPassword(): string { return ''; }
    public function getAuthPasswordName(): string { return 'password'; }
    public function getRememberToken(): ?string { return null; }
    public function setRememberToken($value): void {}
    public function getRememberTokenName(): string { return ''; }
}

class FakeCreateRequestPermissionModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeCreateRequestPermission extends FakeCreateRequestPermissionModelBase
{
    public function __construct(
        public readonly int $id,
        public string $slug,
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }
}

class FakeCreateRequestPermissionUserModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

class FakeCreateRequestPermissionRequestModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeCreateRequestPermissionRequest extends FakeCreateRequestPermissionRequestModelBase
{
    public function __construct(
        public readonly int $id,
        public int $userId,
        public int $permissionId
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }
}