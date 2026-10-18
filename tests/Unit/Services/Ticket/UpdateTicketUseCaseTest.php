<?php

namespace Tests\Unit\Services\Ticket;

use App\DTOs\Ticket\UpdateTicketDTO;
use App\Services\Ticket\UpdateTicketUseCase;
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

class UpdateTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_updates_ticket_inside_transaction(): void
    {
        $id = 123;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $dto = $this->makeDto();
        $ticket = Mockery::mock();
        $ticket->version = $dto->version;

        $ticket->shouldReceive('fill')
            ->once()
            ->with($dto->toArray())
            ->andReturnSelf();

        $ticket->shouldReceive('save')
            ->once()
            ->andReturn(true);

        $ticket->shouldReceive('refresh')
            ->once()
            ->andReturnSelf();

        $ticket->shouldReceive('load')
            ->once()
            ->andReturnSelf();

        $this->mockTicketLookup($id, $ticket);
        $this->mockStatusExists(true);

        $result = (new UpdateTicketUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket update failed.'
        );

        $this->assertSame($ticket, $result->getValue());
        $this->assertSame(4, $ticket->version);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new UpdateTicketUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

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

        $result = (new UpdateTicketUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_ticket_version_is_outdated(): void
    {
        $id = 123;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $dto = $this->makeDto(version: 3);

        $ticket = Mockery::mock();
        $ticket->version = 4;

        $ticket->shouldNotReceive('fill');
        $ticket->shouldNotReceive('save');

        $this->mockTicketLookup($id, $ticket);

        $result = (new UpdateTicketUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'This ticket has been modified by another user.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_no_fields_were_provided(): void
    {
        $id = 123;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $dto = UpdateTicketDTO::fromArray([
            'version' => 3,
        ]);

        $ticket = Mockery::mock();
        $ticket->version = 3;

        $ticket->shouldNotReceive('fill');
        $ticket->shouldNotReceive('save');

        $this->mockTicketLookup($id, $ticket);

        $result = (new UpdateTicketUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'No ticket fields were provided for updating.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_status_does_not_exist_or_is_inactive(): void
    {
        $id = 123;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $dto = $this->makeDto();

        $ticket = Mockery::mock();
        $ticket->version = $dto->version;

        $ticket->shouldNotReceive('fill');
        $ticket->shouldNotReceive('save');

        $this->mockTicketLookup($id, $ticket);
        $this->mockStatusExists(false);

        $result = (new UpdateTicketUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'The selected status does not exist or is inactive.',
            $result->getError() ?? ''
        );
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
            ->with(Mockery::type(Closure::class), 3)
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'update tickets',
                    [],
                    new PDOException('Database failure.')
                )
            );

        $result = (new UpdateTicketUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while updating the ticket.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(int $version = 3): UpdateTicketDTO
    {
        return UpdateTicketDTO::fromArray([
            'version' => $version,
            'status_id' => 20,
            'first_response_at' => '2026-10-18 10:00:00',
            'resolved_at' => null,
            'closed_at' => null,
        ]);
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.update')
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
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                static fn (Closure $callback, int $attempts) => $callback()
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

    private function mockStatusExists(bool $exists): void
    {
        $model = Mockery::mock('alias:App\Models\TicketStatusModel');
        $query = Mockery::mock();

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('whereNull')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('exists')
            ->once()
            ->andReturn($exists);
   }
}