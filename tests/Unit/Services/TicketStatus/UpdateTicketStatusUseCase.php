<?php

namespace Tests\Unit\Services\TicketStatus;

use App\DTOs\TicketStatus\UpdateTicketStatusDTO;
use App\Services\TicketStatus\UpdateTicketStatusUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class UpdateTicketStatusUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_updates_status_inside_transaction(): void
    {
        $id = 123;
        $dto = $this->makeDto();
        $status = $this->makeStatus();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockStatusLookup($id, $status);

        $result = (new UpdateTicketStatusUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket status update failed.'
        );

        $this->assertSame($status, $result->getValue());
        $this->assertSame($dto->name, $status->name);
        $this->assertSame($dto->code, $status->code);
        $this->assertSame($dto->description, $status->description);
        $this->assertSame($dto->color, $status->color);
        $this->assertSame($dto->icon, $status->icon);
        $this->assertSame($dto->displayOrder, $status->display_order);
        $this->assertSame($dto->isInitial, $status->is_initial);
        $this->assertSame($dto->isFinal, $status->is_final);
        $this->assertSame($dto->stopsSla, $status->stops_sla);
        $this->assertSame($dto->requiresComment, $status->requires_comment);
        $this->assertSame($dto->isActive, $status->is_active);
        $this->assertSame(1, $status->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new UpdateTicketStatusUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

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

        $result = (new UpdateTicketStatusUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket status not found.',
            $result->getError() ?? ''
        );

        $this->assertSame(404, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $id = 123;

        $idGenerator = $this->mockIdValidation($id, true);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'update ticket_statuses',
                    [],
                    new PDOException('Database failure.')
                )
            );

        $result = (new UpdateTicketStatusUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while updating the ticket status.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(): UpdateTicketStatusDTO
    {
        return new UpdateTicketStatusDTO(
            id: 123,
            name: 'Resolved',
            code: 'RESOLVED',
            description: 'Ticket has been resolved.',
            color: '#22AA66',
            icon: 'check-circle',
            displayOrder: 20,
            isInitial: false,
            isFinal: true,
            stopsSla: true,
            requiresComment: true,
            isActive: true,
        );
    }

    private function mockIdValidation(
        int $id,
        bool $isValid
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
        mixed $status
    ): void {
        $model = Mockery::mock('alias:App\Models\TicketStatusModel');
        $query = Mockery::mock();

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('whereKey')
            ->once()
            ->with($id)
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('first')
            ->once()
            ->andReturn($status);
    }

    private function makeStatus(): object
    {
        return new class {
            public mixed $name = null;
            public mixed $code = null;
            public mixed $description = null;
            public mixed $color = null;
            public mixed $icon = null;
            public mixed $display_order = null;
            public mixed $is_initial = null;
            public mixed $is_final = null;
            public mixed $stops_sla = null;
            public mixed $requires_comment = null;
            public mixed $is_active = null;

            public int $saveCalls = 0;

            public function save(): bool
            {
                $this->saveCalls++;

                return true;
            }
        };
    }
}