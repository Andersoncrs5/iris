<?php

namespace Tests\Unit\Services\TicketStatus;

use App\Services\TicketStatus\DeleteTicketStatusUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use RuntimeException;
use Tests\TestCase;

class DeleteTicketStatusUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_deletes_status_inside_transaction(): void
    {
        $id = 123;
        $status = Mockery::mock();

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockStatusLookup($id, $status);

        $status->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $result = (new DeleteTicketStatusUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket status deletion failed.'
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

        $result = (new DeleteTicketStatusUseCase($idGenerator))
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

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockStatusLookup($id, null);

        $result = (new DeleteTicketStatusUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket status not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_delete_throws_exception(): void
    {
        $id = 123;
        $status = Mockery::mock();

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockStatusLookup($id, $status);

        $status->shouldReceive('delete')
            ->once()
            ->andThrow(new RuntimeException('Simulated failure.'));

        $result = (new DeleteTicketStatusUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Failed to delete ticket status:',
            $result->getError() ?? ''
        );

        $this->assertStringContainsString(
            'Simulated failure.',
            $result->getError() ?? ''
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.status.delete')
            ->andReturn(null);
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
            ->andReturn($status);
    }
}