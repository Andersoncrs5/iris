<?php

namespace Tests\Feature\Services\Permission;

use App\DTOs\Permission\CreatePermissionDTO;
use App\Services\Pemission\CreatePermissionUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;
use Tests\TestCase;

class CreatePermissionUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_creates_permission_and_assigns_it_to_all_super_admins(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();

        $admins = [
            new FakeCreatePermissionUser(501),
            new FakeCreatePermissionUser(502),
        ];

        $permissionId = 1001;

        $permission = new FakeCreatePermissionModel(
            id: $permissionId,
            name: $dto->name,
            slug: $dto->slug,
            module: $dto->module,
            description: $dto->description,
        );

        $this->mockTransaction();
        $this->mockSuperAdminLookup($admins);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->times(3)
            ->andReturn(1001, 1002, 1003);

        $permissionQuery = Mockery::mock();

        $permissionQuery->shouldReceive('create')
            ->once()
            ->andReturn($permission);

        FakePermissionModelBase::$queryBuilder = $permissionQuery;

        $this->mockAssignments(
            admins: $admins,
            assignmentIds: [1002, 1003],
        );

        $useCase = new CreatePermissionUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Permission creation failed.'
        );

        $this->assertSame($permission, $result->getValue());
        $this->assertSame($permissionId, $permission->getKey());
        $this->assertSame($dto->name, $permission->name);
        $this->assertSame($dto->slug, $permission->slug);

        foreach ($admins as $admin) {
            $this->assertContains($dto->slug, $admin->permissions);
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_no_super_admin_exists(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();

        $this->mockTransaction();
        $this->mockSuperAdminLookup([]);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldNotReceive('generate');

        $useCase = new CreatePermissionUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'no Super Admin account was found',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_permission_creation_throws(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();

        $admins = [
            new FakeCreatePermissionUser(501),
        ];

        $this->mockTransaction();
        $this->mockSuperAdminLookup($admins);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(1001);

        $permissionQuery = Mockery::mock();

        $permissionQuery->shouldReceive('create')
            ->once()
            ->andThrow(
                new RuntimeException('Permission creation failed.')
            );

        FakePermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new CreatePermissionUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Permission creation failed.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_assignment_creation_throws(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();

        $admin = new FakeCreatePermissionUser(501);
        $admins = [$admin];

        $permission = new FakeCreatePermissionModel(
            id: 1001,
            name: $dto->name,
            slug: $dto->slug,
            module: $dto->module,
            description: $dto->description,
        );

        $this->mockTransaction();
        $this->mockSuperAdminLookup($admins);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->twice()
            ->andReturn(1001, 1002);

        $permissionQuery = Mockery::mock();

        $permissionQuery->shouldReceive('create')
            ->once()
            ->andReturn($permission);

        FakePermissionModelBase::$queryBuilder = $permissionQuery;

        $this->mockAssignments(
            admins: $admins,
            assignmentIds: [1002],
            failureMessage: 'Permission assignment failed.',
        );

        $useCase = new CreatePermissionUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Permission assignment failed.',
            $result->getError() ?? ''
        );

        $this->assertNotContains($dto->slug, $admin->permissions);
    }

    private function makeDto(): CreatePermissionDTO
    {
        return new CreatePermissionDTO(
            name: 'View reports',
            slug: 'reports.view',
            module: 'reports',
            description: 'Allows viewing reports.',
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeUserModelBase::class,
            \App\Models\User::class
        );

        class_alias(
            FakePermissionModelBase::class,
            \App\Models\PermissionModel::class
        );

        class_alias(
            FakePermissionUserModelBase::class,
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

    private function mockSuperAdminLookup(array $admins): void
    {
        $query = Mockery::mock();
        $filters = Mockery::mock();
        $permissionFilters = Mockery::mock();

        FakeUserModelBase::$queryBuilder = $query;

        $query->shouldReceive('where')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andReturnUsing(
                function (Closure $callback) use ($filters, $query) {
                    $callback($filters);

                    return $query;
                }
            );

        $filters->shouldReceive('whereIn')
            ->once()
            ->andReturnSelf();

        $filters->shouldReceive('orWhereJsonContains')
            ->twice()
            ->andReturnSelf();

        $filters->shouldReceive('orWhereHas')
            ->once()
            ->with(
                'permissionRelations',
                Mockery::type(Closure::class)
            )
            ->andReturnUsing(
                function (string $relation, Closure $callback) use (
                    $permissionFilters,
                    $filters
                ) {
                    $callback($permissionFilters);

                    return $filters;
                }
            );

        $permissionFilters->shouldReceive('whereIn')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('get')
            ->once()
            ->andReturn(new EloquentCollection($admins));
    }

    private function mockAssignments(
        array $admins,
        array $assignmentIds,
        ?string $failureMessage = null,
    ): void {
        $lookupQuery = Mockery::mock();
        $createQuery = Mockery::mock();

        FakePermissionUserModelBase::$withTrashedBuilder = $lookupQuery;
        FakePermissionUserModelBase::$queryBuilder = $createQuery;

        /*
         * Não validamos os argumentos de where().
         * O importante aqui é permitir a cadeia de chamadas
         * e devolver o próprio mock para continuar a consulta.
         */
        $lookupQuery->shouldReceive('where')
            ->times(count($admins) * 2)
            ->andReturnSelf();

        $lookupQuery->shouldReceive('first')
            ->times(count($admins))
            ->andReturn(null);

        foreach ($admins as $index => $admin) {
            $createExpectation = $createQuery->shouldReceive('create')
                ->once();

            if ($failureMessage !== null) {
                $createExpectation->andThrow(
                    new RuntimeException($failureMessage)
                );
            } else {
                $createExpectation->andReturn(new \stdClass());
            }
        }
    }
}

/*
|--------------------------------------------------------------------------
| Modelos falsos usados somente nos testes
|--------------------------------------------------------------------------
*/

class FakeUserModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeCreatePermissionUser extends FakeUserModelBase
{
    public array $permissions = [];

    public function __construct(
        private readonly int $id
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }

    public function addPermission(string $slug): bool
    {
        if (!in_array($slug, $this->permissions, true)) {
            $this->permissions[] = $slug;
        }

        return true;
    }
}

class FakePermissionModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeCreatePermissionModel extends FakePermissionModelBase
{
    public function __construct(
        private readonly int $id,
        public string $name,
        public string $slug,
        public ?string $module = null,
        public ?string $description = null,
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }
}

class FakePermissionUserModelBase
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