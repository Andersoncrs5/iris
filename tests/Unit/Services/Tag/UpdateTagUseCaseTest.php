<?php

namespace Tests\Unit\Services\Tag;

use App\DTOs\Tag\UpdateTagDTO;
use App\Services\Tag\UpdateTagUseCase;
use Closure;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;
use Tests\TestCase;

class UpdateTagUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_updates_tag_inside_transaction(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');
        $query = Mockery::mock();
        $tag = Mockery::mock();

        $dto = $this->makeDto();

        $tagModel->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('first')
            ->once()
            ->andReturn($tag);

        $tag->shouldReceive('fill')
            ->once()
            ->with([
                'name' => $dto->name,
                'slug' => $dto->slug,
                'color' => $dto->color,
                'type' => $dto->type,
                'description' => $dto->description,
                'is_active' => $dto->isActive,
            ])
            ->andReturnSelf();

        $tag->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $this->mockTransaction();

        $result = (new UpdateTagUseCase())->execute($dto, 123);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Tag update failed.'
        );

        $this->assertSame($tag, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_tag_does_not_exist(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');
        $query = Mockery::mock();

        $tagModel->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $this->mockTransaction();

        $result = (new UpdateTagUseCase())->execute(
            $this->makeDto(),
            123
        );

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Tag not found.',
            $result->getError() ?? ''
        );

        $this->assertSame(404, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_an_exception_occurs(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');
        $query = Mockery::mock();

        $tagModel->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('first')
            ->once()
            ->andThrow(new RuntimeException('Simulated failure.'));

        $this->mockTransaction();

        $result = (new UpdateTagUseCase())->execute(
            $this->makeDto(),
            123
        );

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Failed to update tag.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(): UpdateTagDTO
    {
        return new UpdateTagDTO(
            'Updated tag',
            'updated-tag',
            '#FF5733',
            'GENERAL',
            'Updated description',
            true,
        );
    }

    private function mockTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->with(
                Mockery::type(Closure::class),
                3
            )
            ->andReturnUsing(
                static fn (Closure $callback, int $attempts) => $callback()
            );
    }
}