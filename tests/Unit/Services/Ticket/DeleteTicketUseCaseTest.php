<?php

namespace Tests\Unit\Services\Ticket;

use App\Services\Ticket\DeleteTicketUseCase;
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

class DeleteTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_deletes_ticket_inside_transaction(): void
    {
        $this->mockAuthorization();
        $this->mockTransaction();

        $ticket = Mockery::mock();

        $ticket->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->mockTicketLookup(123, $ticket);

        $result = (new DeleteTicketUseCase())->execute(123);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket deletion failed.'
        );

        $this->assertSame($ticket, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_does_not_exist(): void
    {
        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockTicketLookup(123, null);

        $result = (new DeleteTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket not found.',
            $result->getError() ?? ''
        );
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
                    'delete from tickets where id = ?',
                    [123],
                    new PDOException('Database failure.')
                )
            );

        $result = (new DeleteTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Unable to delete the ticket due to a database error.',
            $result->getError() ?? ''
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.delete')
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
}