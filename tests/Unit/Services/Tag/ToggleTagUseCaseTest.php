<?php

namespace Tests\Unit\Services\Tag;

use App\Services\Tag\ToggleTagUseCase;
use Closure;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class ToggleTagUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_toggles_tag_from_active_to_inactive(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');

        $tag = new class {
            public bool $is_active = true;
            public int $saveCalls = 0;

            public function save(): bool
            {
                $this->saveCalls++;

                return true;
            }
        };

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($tag);

        $this->mockTransaction();

        $result = (new ToggleTagUseCase())->execute(123);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Failed to toggle tag.'
        );

        $this->assertSame($tag, $result->getValue());
        $this->assertFalse($tag->is_active);
        $this->assertSame(1, $tag->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_toggles_tag_from_inactive_to_active(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');

        $tag = new class {
            public bool $is_active = false;
            public int $saveCalls = 0;

            public function save(): bool
            {
                $this->saveCalls++;

                return true;
            }
        };

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($tag);

        $this->mockTransaction();

        $result = (new ToggleTagUseCase())->execute(123);

        $this->assertTrue($result->isSuccess());
        $this->assertTrue($tag->is_active);
        $this->assertSame(1, $tag->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_tag_does_not_exist(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn(null);

        $this->mockTransaction();

        $result = (new ToggleTagUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Tag not found.',
            $result->getError() ?? ''
        );

        $this->assertSame(404, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        DB::shouldReceive('transaction')->never();

        $result = (new ToggleTagUseCase())->execute(0);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Id should be required',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
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