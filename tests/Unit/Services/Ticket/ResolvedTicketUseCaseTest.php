<?php

namespace Tests\Unit\Services\Ticket;

use App\Services\Ticket\ResolvedTicketUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class ResolvedTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_resolves_ticket_successfully(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $ticket = $this->makeTicket();

        $this->mockTicketLookup($id, $ticket);

        $this->mockStatuses(
            currentCode: 'OPEN',
            resolvedStatus: (object) ['id' => 20],
        );

        $result = (new ResolvedTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket resolution failed.'
        );

        $this->assertSame($ticket, $result->getValue());
        $this->assertSame(20, $ticket->status_id);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertNotNull($ticket->last_activity_at);
        $this->assertSame(4, $ticket->version);
        $this->assertSame(1, $ticket->saveCalls);
        $this->assertTrue($ticket->refreshed);

        $this->assertSame(
            ['user', 'agent', 'category', 'status', 'priority'],
            $ticket->loadedRelations
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new ResolvedTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid ticket ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_does_not_exist(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockTicketLookup($id, null);

        $result = (new ResolvedTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_is_closed(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $ticket = $this->makeTicket();

        $this->mockTicketLookup($id, $ticket);
        $this->mockCurrentStatus('CLOSED');

        $result = (new ResolvedTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'A closed ticket cannot be resolved.',
            $result->getError() ?? ''
        );

        $this->assertSame(0, $ticket->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_is_already_resolved(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $ticket = $this->makeTicket(
            resolvedAt: '2026-01-01 10:00:00',
        );

        $this->mockTicketLookup($id, $ticket);
        $this->mockCurrentStatus('OPEN');

        $result = (new ResolvedTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'This ticket is already resolved.',
            $result->getError() ?? ''
        );

        $this->assertSame(0, $ticket->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_active_resolved_status_is_not_configured(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $ticket = $this->makeTicket();

        $this->mockTicketLookup($id, $ticket);

        $this->mockStatuses(
            currentCode: 'OPEN',
            resolvedStatus: null,
        );

        $result = (new ResolvedTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'No active RESOLVED status is configured.',
            $result->getError() ?? ''
        );

        $this->assertSame(0, $ticket->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'select * from tickets where id = ?',
                    [$id],
                    new PDOException('Database failure.')
                )
            );

        $result = (new ResolvedTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Unable to resolve the ticket due to a database error.',
            $result->getError() ?? ''
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.resolve')
            ->andReturn(null);
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
            ->with(Mockery::type(Closure::class))
            ->andReturnUsing(
                static fn (Closure $callback) => $callback()
            );
    }

    private function mockTicketLookup(int $id, mixed $ticket): void
    {
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

    private function mockCurrentStatus(string $code): void
    {
        $model = Mockery::mock('alias:App\Models\TicketStatusModel');
        $query = Mockery::mock();

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('find')
            ->once()
            ->andReturn((object) ['code' => $code]);
    }

    private function mockStatuses(
        string $currentCode,
        mixed $resolvedStatus,
    ): void {
        $model = Mockery::mock('alias:App\Models\TicketStatusModel');

        $currentQuery = Mockery::mock();
        $resolvedQuery = Mockery::mock();

        $model->shouldReceive('query')
            ->twice()
            ->andReturn($currentQuery, $resolvedQuery);

        $currentQuery->shouldReceive('find')
            ->once()
            ->andReturn((object) ['code' => $currentCode]);

        $resolvedQuery->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $resolvedQuery->shouldReceive('whereRaw')
            ->once()
            ->andReturnSelf();

        $resolvedQuery->shouldReceive('first')
            ->once()
            ->andReturn($resolvedStatus);
    }

    private function makeTicket(
        ?string $resolvedAt = null,
        ?string $closedAt = null,
        int $version = 3,
    ): object {
        return new class(
            10,
            $resolvedAt,
            $closedAt,
            $version,
        ) {
            public mixed $last_activity_at = null;
            public int $saveCalls = 0;
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
                $this->saveCalls++;

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