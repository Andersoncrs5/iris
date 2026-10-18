<?php

namespace Tests\Unit\Services\Category;

use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Services\Category\ToggleCategoryUseCase;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class ToggleCategoryUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /*
    public function test_it_deactivates_an_active_category(): void
    {
        $id = 123;
        $expectedActive = false;

        $this->authorizeCategoryUpdate();

        $category = new CategoryModel();
        $category->is_active = true;

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($category);

        $repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(
                fn ($model): bool =>
                    $model instanceof CategoryModel
                    && (bool) $model->is_active === $expectedActive
            ))
            ->andReturn($category);

        $this->mockTransaction();

        $useCase = new ToggleCategoryUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($category, $result->getValue());
        $this->assertFalse((bool) $result->getValue()->is_active);
        $this->assertNull($result->getCode());
    }

    public function test_it_activates_an_inactive_category(): void
    {
        $id = 123;
        $expectedActive = true;

        $this->authorizeCategoryUpdate();

        $category = new CategoryModel();
        $category->is_active = false;

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($category);

        $repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(
                fn ($model): bool =>
                    $model instanceof CategoryModel
                    && (bool) $model->is_active === $expectedActive
            ))
            ->andReturn($category);

        $this->mockTransaction();

        $useCase = new ToggleCategoryUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($category, $result->getValue());
        $this->assertTrue((bool) $result->getValue()->is_active);
        $this->assertNull($result->getCode());
    }

    public function test_it_rejects_zero_and_negative_ids(): void
    {
        $this->authorizeCategoryUpdate(2);

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldNotReceive('findById');
        $repository->shouldNotReceive('update');

        DB::shouldReceive('transaction')->never();

        $useCase = new ToggleCategoryUseCase($repository);

        foreach ([0, -1] as $id) {
            $result = $useCase->execute($id);

            $this->assertTrue($result->isFailure());
            $this->assertSame(
                'A valid category ID is required.',
                $result->getError()
            );
            $this->assertSame(400, $result->getCode());
        }
    }

    public function test_it_returns_not_found_when_category_does_not_exist(): void
    {
        $id = 999;

        $this->authorizeCategoryUpdate();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn(null);

        $repository->shouldNotReceive('update');

        $this->mockTransaction();

        $useCase = new ToggleCategoryUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'Category not found.',
            $result->getError()
        );
        $this->assertSame(404, $result->getCode());
    }

    public function test_it_returns_failure_when_repository_update_fails(): void
    {
        $id = 123;
        $expectedActive = false;

        $this->authorizeCategoryUpdate();

        $category = new CategoryModel();
        $category->is_active = true;

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($category);

        $repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(
                fn ($model): bool =>
                    $model instanceof CategoryModel
                    && (bool) $model->is_active === $expectedActive
            ))
            ->andReturn(false);

        $this->mockTransaction();

        $useCase = new ToggleCategoryUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'Failed to update category status.',
            $result->getError()
        );
        $this->assertSame(500, $result->getCode());
    }

    public function test_it_handles_repository_exceptions(): void
    {
        $id = 123;

        $this->authorizeCategoryUpdate();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andThrow(
                new RuntimeException('Database failure.')
            );

        $this->mockTransaction();

        $useCase = new ToggleCategoryUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected error occurred while updating the category status.',
            $result->getError()
        );
        $this->assertSame(500, $result->getCode());
    }

    public function test_it_requires_category_update_authorization(): void
    {
        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldNotReceive('findById');
        $repository->shouldNotReceive('update');

        DB::shouldReceive('transaction')->never();

        Gate::shouldReceive('authorize')
            ->once()
            ->with('category.update')
            ->andThrow(new AuthorizationException());

        $useCase = new ToggleCategoryUseCase($repository);

        $this->expectException(AuthorizationException::class);

        $useCase->execute(123);
    }

    private function authorizeCategoryUpdate(int $times = 1): void
    {
        Gate::shouldReceive('authorize')
            ->times($times)
            ->with('category.update')
            ->andReturn(null);
    }

    private function mockTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                function (Closure $callback, int $attempts) {
                    $this->assertSame(3, $attempts);

                    return $callback();
                }
            );
    }
    */
}
