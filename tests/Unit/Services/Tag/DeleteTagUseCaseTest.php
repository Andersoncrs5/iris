<?php

namespace Tests\Unit\Services\Tag;

use App\Services\Tag\DeleteTagUseCase;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;
use Tests\TestCase;

class DeleteTagUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_deletes_tag_inside_transaction(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');
        $tag = Mockery::mock();

        Gate::shouldReceive('authorize')
            ->once()
            ->with('tag.delete');

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($tag);

        $tag->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->mockTransaction();

        $result = (new DeleteTagUseCase())->execute(123);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Tag deletion failed.'
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_tag_does_not_exist(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');

        Gate::shouldReceive('authorize')
            ->once()
            ->with('tag.delete');

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn(null);

        $this->mockTransaction();

        $result = (new DeleteTagUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Tag not found',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_delete_throws_exception(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');
        $tag = Mockery::mock();

        Gate::shouldReceive('authorize')
            ->once()
            ->with('tag.delete');

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($tag);

        $tag->shouldReceive('delete')
            ->once()
            ->andThrow(new RuntimeException('Simulated failure.'));

        $this->mockTransaction();

        $result = (new DeleteTagUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Failed to delete tag:',
            $result->getError() ?? ''
        );

        $this->assertStringContainsString(
            'Simulated failure.',
            $result->getError() ?? ''
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