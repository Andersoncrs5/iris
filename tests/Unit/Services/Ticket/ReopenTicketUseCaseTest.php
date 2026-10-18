<?php

namespace Tests\Unit\Services\Ticket;

use App\Services\Ticket\ReopenTicketUseCase;
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

class ReopenTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_reopens_ticket_using_configured_status(): void
    {
        $id = 123;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $ticket = $this->makeTicket();

        $this->mockTicketLookup($id, $ticket);

        $this->mockStatusesForReopen(
            currentCode: 'resolved',
            targetStatus: (object) ['id' => 20],
        );

        $result = (new ReopenTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket reopening failed.'
        );

        $this->assertSame($ticket, $result->getValue());
        $this->assertSame(20, $ticket->status_id);
        $this->assertNull($ticket->resolved_at);
        $this->assertNull($ticket->closed_at);
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

        $result = (new ReopenTicketUseCase($idGenerator))
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

        $result = (new ReopenTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_cannot_be_reopened(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $ticket = $this->makeTicket(
            resolvedAt: null,
            closedAt: null,
        );

        $this->mockTicketLookup($id, $ticket);
        $this->mockCurrentStatus('OPEN');

        $result = (new ReopenTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Only resolved or closed tickets can be reopened.',
            $result->getError() ?? ''
        );

        $this->assertSame(0, $ticket->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_uses_active_initial_status_as_fallback(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $ticket = $this->makeTicket();

        $this->mockTicketLookup($id, $ticket);

        $this->mockStatusesForReopen(
            currentCode: 'RESOLVED',
            targetStatus: null,
            fallbackStatus: (object) ['id' => 30],
        );

        $result = (new ReopenTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket reopening failed.'
        );

        $this->assertSame(30, $ticket->status_id);
        $this->assertSame(1, $ticket->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_no_active_reopening_status_exists(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();

        $ticket = $this->makeTicket();

        $this->mockTicketLookup($id, $ticket);

        $this->mockStatusesForReopen(
            currentCode: 'RESOLVED',
            targetStatus: null,
            fallbackStatus: null,
        );

        $result = (new ReopenTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'No active status is configured for reopening tickets.',
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

        $result = (new ReopenTicketUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Unable to reopen the ticket due to a database error.',
            $result->getError() ?? ''
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.reopen')
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

    private function mockStatusesForReopen(
        string $currentCode,
        mixed $targetStatus,
        mixed $fallbackStatus = null,
    ): void {
        $model = Mockery::mock('alias:App\Models\TicketStatusModel');

        $currentQuery = Mockery::mock();
        $targetQuery = Mockery::mock();
        $fallbackQuery = Mockery::mock();

        $queryCount = $targetStatus === null ? 3 : 2;

        $model->shouldReceive('query')
            ->times($queryCount)
            ->andReturn(
                $currentQuery,
                $targetQuery,
                $fallbackQuery,
            );

        $currentQuery->shouldReceive('find')
            ->once()
            ->andReturn((object) ['code' => $currentCode]);

        // A consulta de REOPENED/OPEN chama where() apenas uma vez.
        $targetQuery->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $targetQuery->shouldReceive('whereRaw')
            ->once()
            ->andReturnSelf();

        $targetQuery->shouldReceive('orderByRaw')
            ->once()
            ->andReturnSelf();

        $targetQuery->shouldReceive('first')
            ->once()
            ->andReturn($targetStatus);

        if ($targetStatus === null) {
            // O fallback filtra por is_active e is_initial.
            $fallbackQuery->shouldReceive('where')
                ->twice()
                ->andReturnSelf();

            $fallbackQuery->shouldReceive('first')
                ->once()
                ->andReturn($fallbackStatus);
        }
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