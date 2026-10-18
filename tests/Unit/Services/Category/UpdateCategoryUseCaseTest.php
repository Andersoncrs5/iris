<?php

namespace Tests\Unit\Services\Category;

use App\DTOs\Category\UpdateCategoryDTO;
use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Services\Category\UpdateCategoryUseCase;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class UpdateCategoryUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_updates_category_successfully(): void
    {
        $id = 123;
        $dto = $this->makeDto();

        $this->authorizeCategoryUpdate();

        $category = $this->makeCategory($id);

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($category);

        $repository->shouldReceive('update')
            ->once()
            ->andReturnUsing(
                fn (CategoryModel $model) => $model
            );

        $this->mockTransaction();

        $useCase = new UpdateCategoryUseCase($repository);

        $result = $useCase->execute($dto, $id);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($category, $result->getValue());

        $this->assertSame('Updated category', $category->name);
        $this->assertSame('updated-category', $category->slug);
        $this->assertSame('Updated description', $category->description);
        $this->assertNull($category->parent_id);
        $this->assertSame('fa-solid fa-folder', $category->icon);
        $this->assertSame(10, (int) $category->display_order);
        $this->assertTrue((bool) $category->is_active);
    }

    public function test_it_rejects_zero_and_negative_ids(): void
    {
        $dto = $this->makeDto();

        $this->authorizeCategoryUpdate(2);

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldNotReceive('findById');
        $repository->shouldNotReceive('update');

        DB::shouldReceive('transaction')->never();

        $useCase = new UpdateCategoryUseCase($repository);

        foreach ([0, -1] as $id) {
            $result = $useCase->execute($dto, $id);

            $this->assertTrue($result->isFailure());
            $this->assertSame(
                'A valid category ID is required.',
                $result->getError()
            );
            $this->assertSame(400, $result->getCode());
        }
    }

    public function test_it_rejects_an_invalid_parent_id(): void
    {
        $dto = $this->makeDto(parentId: 0);

        $this->authorizeCategoryUpdate();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldNotReceive('findById');
        $repository->shouldNotReceive('update');

        DB::shouldReceive('transaction')->never();

        $useCase = new UpdateCategoryUseCase($repository);

        $result = $useCase->execute($dto, 123);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'A valid parent category ID is required.',
            $result->getError()
        );
        $this->assertSame(400, $result->getCode());
    }

    public function test_it_returns_not_found_when_category_does_not_exist(): void
    {
        $id = 999;
        $dto = $this->makeDto();

        $this->authorizeCategoryUpdate();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn(null);

        $repository->shouldNotReceive('update');

        $this->mockTransaction();

        $useCase = new UpdateCategoryUseCase($repository);

        $result = $useCase->execute($dto, $id);

        $this->assertTrue($result->isFailure());
        $this->assertSame('Category not found.', $result->getError());
        $this->assertSame(404, $result->getCode());
    }

    public function test_it_rejects_a_category_as_its_own_parent(): void
    {
        $id = 123;
        $dto = $this->makeDto(parentId: $id);

        $this->authorizeCategoryUpdate();

        $category = $this->makeCategory($id);

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($category);

        $repository->shouldNotReceive('update');

        $this->mockTransaction();

        $useCase = new UpdateCategoryUseCase($repository);

        $result = $useCase->execute($dto, $id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'A category cannot be its own parent.',
            $result->getError()
        );
        $this->assertSame(400, $result->getCode());
    }

    public function test_it_rejects_a_nonexistent_parent_category(): void
    {
        $id = 123;
        $parentId = 456;
        $dto = $this->makeDto(parentId: $parentId);

        $this->authorizeCategoryUpdate();

        $category = $this->makeCategory($id);

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($category);

        $repository->shouldReceive('findById')
            ->once()
            ->with($parentId)
            ->andReturn(null);

        $repository->shouldNotReceive('update');

        $this->mockTransaction();

        $useCase = new UpdateCategoryUseCase($repository);

        $result = $useCase->execute($dto, $id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'Parent category not found.',
            $result->getError()
        );
        $this->assertSame(404, $result->getCode());
    }

    public function test_it_rejects_a_parent_that_would_create_a_cycle(): void
    {
        $categoryId = 10;
        $parentId = 20;
        $ancestorId = 30;

        $dto = $this->makeDto(parentId: $parentId);

        $this->authorizeCategoryUpdate();

        $category = $this->makeCategory($categoryId);

        $parent = $this->makeCategory($parentId);
        $parent->parent_id = $ancestorId;

        $ancestor = $this->makeCategory($ancestorId);
        $ancestor->parent_id = $categoryId;

        $repository = Mockery::mock(ICategoryRepository::class);

        // First load: category being updated.
        // Second load: ancestor chain reaches the same category.
        $repository->shouldReceive('findById')
            ->twice()
            ->with($categoryId)
            ->andReturn($category);

        $repository->shouldReceive('findById')
            ->once()
            ->with($parentId)
            ->andReturn($parent);

        $repository->shouldReceive('findById')
            ->once()
            ->with($ancestorId)
            ->andReturn($ancestor);

        $repository->shouldNotReceive('update');

        $this->mockTransaction();

        $useCase = new UpdateCategoryUseCase($repository);

        $result = $useCase->execute($dto, $categoryId);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'The selected parent would create a category hierarchy cycle.',
            $result->getError()
        );
        $this->assertSame(400, $result->getCode());
    }

    public function test_it_handles_repository_exceptions(): void
    {
        $id = 123;
        $dto = $this->makeDto();

        $this->authorizeCategoryUpdate();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andThrow(new RuntimeException('Database failure.'));

        $this->mockTransaction();

        $useCase = new UpdateCategoryUseCase($repository);

        $result = $useCase->execute($dto, $id);

        $this->assertTrue($result->isFailure());
        $this->assertSame(
            'An unexpected error occurred while updating the category.',
            $result->getError()
        );
        $this->assertSame(500, $result->getCode());
    }

    public function test_it_requires_update_authorization(): void
    {
        $dto = $this->makeDto();

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldNotReceive('findById');
        $repository->shouldNotReceive('update');

        DB::shouldReceive('transaction')->never();

        Gate::shouldReceive('authorize')
            ->once()
            ->with('category.update')
            ->andThrow(new AuthorizationException());

        $useCase = new UpdateCategoryUseCase($repository);

        $this->expectException(AuthorizationException::class);

        $useCase->execute($dto, 123);
    }

    private function makeDto(?int $parentId = null): UpdateCategoryDTO
    {
        return new UpdateCategoryDTO(
            id: null,
            name: 'Updated category',
            slug: 'updated-category',
            description: 'Updated description',
            parentId: $parentId,
            icon: 'fa-solid fa-folder',
            displayOrder: 10,
            isActive: true,
        );
    }

    private function makeCategory(int $id): CategoryModel
    {
        $category = new CategoryModel();
        $category->id = $id;

        return $category;
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
}
