<?php

namespace Tests\Unit\Services\Ticket;

use App\Services\Ticket\CloseTicketUseCase;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class CloseTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_closes_a_resolved_ticket(): void
    {
        $ticket = $this->makeTicket(
            statusId: 10,
            resolvedAt: null,
        );

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockTicketLookup(123, $ticket);
        $this->mockStatusLookup(
            code: 'resolved',
            lookupClosedStatus: true,
            closedStatus: (object) ['id' => 20],
        );

        $result = (new CloseTicketUseCase())->execute(123);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket closing failed.'
        );

        $this->assertSame($ticket, $result->getValue());
        $this->assertSame(20, $ticket->status_id);
        $this->assertNotNull($ticket->closed_at);
        $this->assertNotNull($ticket->last_activity_at);
        $this->assertSame(4, $ticket->version);
        $this->assertTrue($ticket->saved);
        $this->assertTrue($ticket->refreshed);

        $this->assertSame([
            'user',
            'agent',
            'category',
            'status',
            'priority',
        ], $ticket->loadedRelations);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_does_not_exist(): void
    {
        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockTicketLookup(123, null);

        $result = (new CloseTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_is_already_closed(): void
    {
        $ticket = $this->makeTicket(
            closedAt: '2026-01-01 12:00:00',
        );

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockTicketLookup(123, $ticket);
        $this->mockStatusLookup('RESOLVED');

        $result = (new CloseTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'This ticket is already closed.',
            $result->getError() ?? ''
        );

        $this->assertFalse($ticket->saved);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_is_not_resolved(): void
    {
        $ticket = $this->makeTicket(
            resolvedAt: null,
            closedAt: null,
        );

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockTicketLookup(123, $ticket);
        $this->mockStatusLookup('OPEN');

        $result = (new CloseTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Only resolved tickets can be closed.',
            $result->getError() ?? ''
        );

        $this->assertFalse($ticket->saved);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_active_closed_status_is_not_configured(): void
    {
        $ticket = $this->makeTicket();

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockTicketLookup(123, $ticket);
        $this->mockStatusLookup(
            code: 'RESOLVED',
            lookupClosedStatus: true,
            closedStatus: null,
        );

        $result = (new CloseTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'No active CLOSED status is configured.',
            $result->getError() ?? ''
        );

        $this->assertFalse($ticket->saved);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $this->mockAuthorization();

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'select * from tickets where id = ?',
                    [123],
                    new \PDOException('Database failure.')
                )
            );

        $result = (new CloseTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Unable to close the ticket due to a database error.',
            $result->getError() ?? ''
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.close')
            ->andReturn(null);
    }

    private function mockTransaction(): void
    {
        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andReturnUsing(
                static fn (Closure $callback) => $callback()
            );
    }

    private function mockTicketLookup(
        int $id,
        mixed $ticket,
    ): void {
        $model = Mockery::mock('alias:App\Models\TicketModel');
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

    private function mockStatusLookup(
        string $code,
        bool $lookupClosedStatus = false,
        mixed $closedStatus = null,
    ): void {
        $model = Mockery::mock('alias:App\Models\TicketStatusModel');

        $currentQuery = Mockery::mock();
        $closedQuery = Mockery::mock();

        $model->shouldReceive('query')
            ->times($lookupClosedStatus ? 2 : 1)
            ->andReturn($currentQuery, $closedQuery);

        $currentQuery->shouldReceive('find')
            ->once()
            ->andReturn((object) ['code' => $code]);

        if (!$lookupClosedStatus) {
            return;
        }

        $closedQuery->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $closedQuery->shouldReceive('whereRaw')
            ->once()
            ->andReturnSelf();

        $closedQuery->shouldReceive('first')
            ->once()
            ->andReturn($closedStatus);
    }

    private function makeTicket(
        int $statusId = 10,
        ?string $resolvedAt = '2026-01-01 10:00:00',
        ?string $closedAt = null,
        int $version = 3,
    ): object {
        return new class(
            $statusId,
            $resolvedAt,
            $closedAt,
            $version,
        ) {
            public mixed $last_activity_at = null;
            public bool $saved = false;
            public bool $refreshed = false;
            public array $loadedRelations = [];

            public function __construct(
                public int $status_id,
                public ?string $resolved_at,
                public ?string $closed_at,
                public int $version,
            ) {}

            public function save(): bool
            {
                $this->saved = true;

                return true;
            }

            public function refresh(): self
            {
                $this->refreshed = true;

                return $this;
            }

            public function load(array $relations): self
            {
                $this->loadedRelations = $relations;

                return $this;
            }
        };
    }
}