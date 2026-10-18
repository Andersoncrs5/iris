<?php

namespace Tests\Unit\Services\Ticket;

use App\Services\Ticket\DeleteOwnTicketUseCase;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PDOException;
use Tests\TestCase;

class DeleteOwnTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_deletes_own_ticket_inside_transaction(): void
    {
        $this->mockAuthorization();
        $this->mockAuthenticatedUser(100);
        $this->mockTransaction();

        $ticket = Mockery::mock();
        $ticket->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->mockTicketLookup(123, 100, $ticket);

        $result = (new DeleteOwnTicketUseCase())->execute(123);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket deletion failed.'
        );

        $this->assertSame($ticket, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_user_is_not_authenticated(): void
    {
        $this->mockAuthorization();
        $this->mockAuthenticatedUser(null);

        DB::shouldReceive('transaction')->never();

        $result = (new DeleteOwnTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Authentication is required.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_does_not_exist_or_belongs_to_another_user(): void
    {
        $this->mockAuthorization();
        $this->mockAuthenticatedUser(100);
        $this->mockTransaction();
        $this->mockTicketLookup(123, 100, null);

        $result = (new DeleteOwnTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket not found or you do not have permission to delete it.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $this->mockAuthorization();
        $this->mockAuthenticatedUser(100);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'select * from tickets where id = ?',
                    [123],
                    new PDOException('Database failure.')
                )
            );

        $result = (new DeleteOwnTicketUseCase())->execute(123);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Unable to delete your ticket due to a database error.',
            $result->getError() ?? ''
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.own.delete')
            ->andReturn(null);
    }

    private function mockAuthenticatedUser(?int $userId): void
    {
        Auth::shouldReceive('id')
            ->andReturn($userId);
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

    private function mockTicketLookup(
        int $id,
        int $userId,
        mixed $ticket,
    ): void {
        $model = Mockery::mock('alias:App\Models\TicketModel');
        $query = Mockery::mock();

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('first')
            ->once()
            ->andReturn($ticket);
    }
}