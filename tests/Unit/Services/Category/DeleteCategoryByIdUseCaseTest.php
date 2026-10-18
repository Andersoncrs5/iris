<?php

namespace Tests\Unit\Services\Category;

use App\Repository\Interfaces\ICategoryRepository;
use App\Services\Category\DeleteCategoryByIdUseCase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Closure;
use Tests\TestCase;

class DeleteCategoryByIdUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_deletes_category_successfully(): void
    {
        $id = 123;

        $this->authorizeCategoryDeletion();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('deleteById')
            ->once()
            ->with($id)
            ->andReturn(true);

        $this->mockTransaction();

        $useCase = new DeleteCategoryByIdUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isSuccess());
    }

    public function test_it_returns_not_found_when_category_does_not_exist(): void
    {
        $id = 999;

        $this->authorizeCategoryDeletion();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('deleteById')
            ->once()
            ->with($id)
            ->andReturn(false);

        $this->mockTransaction();

        $useCase = new DeleteCategoryByIdUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'Category not found.',
            $result->getError()
        );
    }

    public function test_it_returns_failure_when_repository_throws_exception(): void
    {
        $id = 123;

        $this->authorizeCategoryDeletion();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('deleteById')
            ->once()
            ->with($id)
            ->andThrow(
                new RuntimeException('Database failure.')
            );

        $this->mockTransaction();

        $useCase = new DeleteCategoryByIdUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected error occurred while deleting the category.',
            $result->getError()
        );
    }

    private function authorizeCategoryDeletion(int $times = 1): void
    {
        Gate::shouldReceive('authorize')
            ->times($times)
            ->with('category.delete')
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
}
