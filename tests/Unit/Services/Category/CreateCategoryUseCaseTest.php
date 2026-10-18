<?php

namespace Tests\Unit\Services\Category;

use App\DTOs\Category\CreateCategoryDTO;
use App\Models\CategoryModel;
use App\Models\Model;
use App\Repository\Interfaces\ICategoryRepository;
use App\Services\Category\CreateCategoryUseCase;
use Closure;
use Illuminate\Support\Facades\DB;
use LogicException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Tests\TestCase;

class CreateCategoryUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_creates_a_category_successfully(): void
    {
        $dto = $this->createDto();
        $expectedData = $dto->toModel();

        $category = new CategoryModel();

        $persistedData = null;

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (mixed $data) use (
                    &$persistedData,
                    $category
                ): CategoryModel {
                    $persistedData = $data;

                    return $category;
                }
            );

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                function (Closure $callback, int $attempts) {
                    return $callback();
                }
            );

        $useCase = new CreateCategoryUseCase($repository);

        $result = $useCase->execute($dto);

        $this->assertSame($category, $result);
        $this->assertNotNull($persistedData);

        $this->assertEquals(
            $this->normalize($expectedData),
            $this->normalize($persistedData)
        );
    }

    public function test_it_propagates_repository_exceptions(): void
    {
        $dto = $this->createDto();

        $exception = new RuntimeException(
            'Failed to persist category.'
        );

        $repository = Mockery::mock(ICategoryRepository::class);

        $repository->shouldReceive('save')
            ->once()
            ->andThrow($exception);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                function (Closure $callback, int $attempts) {
                    return $callback();
                }
            );

        $useCase = new CreateCategoryUseCase($repository);

        try {
            $useCase->execute($dto);

            $this->fail(
                'The repository exception should be propagated.'
            );
        } catch (RuntimeException $th) {
            $this->assertSame($exception, $th);
            $this->assertSame(
                'Failed to persist category.',
                $th->getMessage()
            );
        }
    }

    private function createDto(): CreateCategoryDTO
    {
        $reflection = new ReflectionClass(
            CreateCategoryDTO::class
        );

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();

                continue;
            }

            $arguments[] = $this->valueFor($parameter);
        }

        return $reflection->newInstanceArgs($arguments);
    }

    private function valueFor(
        ReflectionParameter $parameter
    ): mixed {
        $type = $parameter->getType();

        if ($type instanceof ReflectionNamedType) {
            if ($type->allowsNull()) {
                return null;
            }

            if (
                !$type->isBuiltin()
                && enum_exists($type->getName())
            ) {
                $cases = $type->getName()::cases();

                if ($cases !== []) {
                    return $cases[0];
                }
            }
        }

        if (!$type instanceof ReflectionNamedType) {
            throw new LogicException(
                "Unsupported DTO constructor type for parameter: "
                . $parameter->getName()
            );
        }

        if (!$type->isBuiltin()) {
            throw new LogicException(
                "Provide an explicit fixture for parameter: "
                . $parameter->getName()
            );
        }

        $name = strtolower($parameter->getName());

        return match ($type->getName()) {
            'string' => match (true) {
                str_contains($name, 'name'),
                str_contains($name, 'title') => 'General',

                str_contains($name, 'slug'),
                str_contains($name, 'code') => 'general',

                str_contains($name, 'description') => 'Test category',

                str_contains($name, 'color') => '#64748b',

                str_contains($name, 'icon') => 'fa-solid fa-folder',

                default => 'test',
            },

            'int' => 1,
            'float' => 1.0,
            'bool' => true,
            'array' => [],
            'mixed' => null,

            default => throw new LogicException(
                "Unsupported DTO constructor type: "
                . $type->getName()
            ),
        };
    }

    private function normalize(mixed $value): mixed
    {
        if ($value instanceof \Illuminate\Database\Eloquent\Model) {
            return $value->getAttributes();
        }

        return $value;
    }
}
