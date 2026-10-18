<?php

namespace Tests\Unit\Services\TicketStatus;

use App\Services\TicketStatus\ToggleTicketStatusUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class ToggleTicketStatusUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_toggles_active_status_to_inactive(): void
    {
        $id = 123;
        $ticket = $this->makeStatus(true);

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockStatusLookup($id, $ticket);

        $result = (new ToggleTicketStatusUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Status toggle failed.'
        );

        $this->assertSame($ticket, $result->getValue());
        $this->assertFalse($ticket->is_active);
        $this->assertSame(1, $ticket->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_toggles_inactive_status_to_active(): void
    {
        $id = 123;
        $ticket = $this->makeStatus(false);

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockStatusLookup($id, $ticket);

        $result = (new ToggleTicketStatusUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Status toggle failed.'
        );

        $this->assertSame($ticket, $result->getValue());
        $this->assertTrue($ticket->is_active);
        $this->assertSame(1, $ticket->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;
        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new ToggleTicketStatusUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid ticket status ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_status_does_not_exist(): void
    {
        $id = 123;

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockStatusLookup($id, null);

        $result = (new ToggleTicketStatusUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket Status not found.',
            $result->getError() ?? ''
        );

        $this->assertSame(404, $result->getCode());
    }

    private function mockIdValidation(
        int $id,
        bool $isValid,
    ): IdGeneratorInterface {
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with($id)
            ->andReturn($isValid);

        return $idGenerator;
    }

    private function mockTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                static fn (Closure $callback, int $attempts) => $callback()
            );
    }

    private function mockStatusLookup(
        int $id,
        mixed $ticket,
    ): void {
        $model = Mockery::mock(
            'alias:App\Models\TicketStatusModel'
        );

        $query = Mockery::mock();

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('find')
            ->once()
            ->with($id)
            ->andReturn($ticket);
    }

    private function makeStatus(bool $isActive): object
    {
        return new class($isActive) {
            public int $saveCalls = 0;

            public function __construct(
                public bool $is_active,
            ) {}

            public function save(): bool
            {
                $this->saveCalls++;

                return true;
            }
        };
    }
}