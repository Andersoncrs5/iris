<?php

namespace Tests\Unit\Services\Tag;

use App\Services\Tag\FindTagByIdUseCase;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class FindTagByIdUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_tag_when_found(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');
        $tag = new \stdClass();

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn($tag);

        $result = (new FindTagByIdUseCase())->execute(123);

        $this->assertTrue($result->isSuccess());
        $this->assertSame($tag, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_tag_is_not_found(): void
    {
        $tagModel = Mockery::mock('alias:App\Models\TagModel');

        $tagModel->shouldReceive('find')
            ->once()
            ->with(123)
            ->andReturn(null);

        $result = (new FindTagByIdUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Tag not found',
            $result->getError() ?? ''
        );
    }
}