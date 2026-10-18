<?php

namespace Tests\Unit\Services\Category;

use App\Models\CategoryModel;
use App\Repository\Interfaces\ICategoryRepository;
use App\Services\Category\FindCategoryByIdUseCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use RuntimeException;
use Tests\TestCase;

class FindCategoryByIdUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_finds_category_successfully(): void
    {
        $id = 123;

        $category = new CategoryModel([
            'name' => 'General',
        ]);

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn($category);

        $useCase = new FindCategoryByIdUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isSuccess());
        $this->assertFalse($result->isFailure());
        $this->assertSame($category, $result->getValue());
        $this->assertNull($result->getCode());
        $this->assertNull($result->getError());
    }

    public function test_it_returns_not_found_when_category_does_not_exist(): void
    {
        $id = 999;

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andReturn(null);

        $useCase = new FindCategoryByIdUseCase($repository);

        $result = $useCase->execute($id);

        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->isSuccess());
        $this->assertSame('Category not found.', $result->getError());
        $this->assertSame(404, $result->getCode());
    }

    public function test_it_returns_error_when_id_is_null(): void
    {
        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldNotReceive('findById');

        $useCase = new FindCategoryByIdUseCase($repository);

        $result = $useCase->execute(null);

        $this->assertTrue($result->isFailure());
        $this->assertFalse($result->isSuccess());
        $this->assertSame('Id is required.', $result->getError());
        $this->assertSame(400, $result->getCode());
    }

    public function test_it_propagates_repository_exceptions(): void
    {
        $id = 123;

        $exception = new RuntimeException(
            'Database query failed.'
        );

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('findById')
            ->once()
            ->with($id)
            ->andThrow($exception);

        $useCase = new FindCategoryByIdUseCase($repository);

        try {
            $useCase->execute($id);

            $this->fail(
                'The repository exception should be propagated.'
            );
        } catch (RuntimeException $th) {
            $this->assertSame($exception, $th);
            $this->assertSame(
                'Database query failed.',
                $th->getMessage()
            );
        }
    }
}
