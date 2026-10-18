<?php

namespace Tests\Feature\Services\Permission;

use App\DTOs\Permission\UpdatePermissionDTO;
use App\Services\Pemission\UpdatePermissionUseCase;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;
use Tests\TestCase;

class UpdatePermissionUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_id_is_invalid(): void
    {
        $dto = $this->makeDto();
        $useCase = new UpdatePermissionUseCase();

        $result = $useCase->execute($dto, 0);

        $this->assertTrue($result->isFailure());
        $this->assertSame('A valid permission ID is required.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_permission_not_found(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();
        $permissionId = 999;

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

        FakeUpdatePermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new UpdatePermissionUseCase();
        $result = $useCase->execute($dto, $permissionId);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Permission not found.', $result->getError());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_successfully_updates_permission_and_increments_version(): void
    {
        $this->installModelAliases();

        $dto = new UpdatePermissionDTO(
            name: 'Updated Name',
            slug: 'updated-slug',
            module: 'updated-module',
            description: 'Updated description'
        );

        $permissionId = 1001;

        $permission = new FakeUpdatePermissionModel(
            id: $permissionId,
            name: 'Original Name',
            slug: 'original-slug',
            module: 'original-module',
            description: 'Original description',
            version: 1,
        );

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

        FakeUpdatePermissionModelBase::$queryBuilder = $permissionQuery;

        $useCase = new UpdatePermissionUseCase();
        $result = $useCase->execute($dto, $permissionId);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($permission, $result->getValue());
        $this->assertSame('Updated Name', $permission->name);
        $this->assertSame('updated-slug', $permission->slug);
        $this->assertSame('updated-module', $permission->module);
        $this->assertSame('Updated description', $permission->description);
        $this->assertSame(2, $permission->version);
    }


    private function makeDto(): UpdatePermissionDTO
    {
        return new UpdatePermissionDTO(
            name: 'View reports',
            slug: 'reports.view',
            module: 'reports',
            description: 'Allows viewing reports.',
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            FakeUpdatePermissionModelBase::class,
            \App\Models\PermissionModel::class
        );
    }

    private function mockTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
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

class FakeUpdatePermissionModelBase
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

final class FakeUpdatePermissionModel extends FakeUpdatePermissionModelBase
{
    public function __construct(
        private readonly int $id,
        public string $name,
        public string $slug,
        public ?string $module = null,
        public ?string $description = null,
        public int $version = 1,
    ) {}

    public function getKey(): int
    {
        return $this->id;
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
        if (self::$queryBuilder && method_exists(self::$queryBuilder, 'save')) {
            return self::$queryBuilder->save();
        }

        return true;
    }
}