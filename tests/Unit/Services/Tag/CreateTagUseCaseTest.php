<?php

namespace Tests\Unit\Services\Tag;

use App\DTOs\Tag\CreateTagDTO;
use App\Services\Tag\CreateTagUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class CreateTagUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_creates_tag_inside_transaction(): void
    {
        class_alias(
            CreateTagUseCaseTestFakeTagModel::class,
            \App\Models\TagModel::class
        );

        $dto = new CreateTagDTO(
            name: 'Laravel',
            slug: 'laravel',
            color: '#FF5733',
            type: 'TECHNOLOGY',
            description: 'Laravel framework tag.',
            usageCount: 0,
            isActive: true,
            version: 1,
        );

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(12345);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(
                function (Closure $callback, int $attempts) {
                    $this->assertSame(3, $attempts);

                    return $callback();
                }
            );

        $useCase = new CreateTagUseCase($idGenerator);

        $result = $useCase->execute($dto);

        $tag = $result->getValue();

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Tag creation failed.'
        );

        $this->assertInstanceOf(
            \App\Models\TagModel::class,
            $tag
        );

        $this->assertSame(12345, $tag->id);
        $this->assertSame('Laravel', $tag->name);
        $this->assertSame('laravel', $tag->slug);
        $this->assertSame('#FF5733', $tag->color);
        $this->assertSame('TECHNOLOGY', $tag->type);
        $this->assertSame('Laravel framework tag.', $tag->description);
        $this->assertSame(0, $tag->usage_count);
        $this->assertTrue($tag->is_active);
        $this->assertSame(1, $tag->version);
        $this->assertTrue($tag->wasSaved);
    }
}

class CreateTagUseCaseTestFakeTagModel
{
    public mixed $id = null;
    public mixed $name = null;
    public mixed $slug = null;
    public mixed $color = null;
    public mixed $type = null;
    public mixed $description = null;
    public mixed $usage_count = null;
    public mixed $is_active = null;
    public mixed $version = null;

    public bool $wasSaved = false;

    public function save(): bool
    {
        $this->wasSaved = true;

        return true;
    }
}