<?php

namespace Tests\Unit\Services\TicketPriority;

use App\Services\TicketPriority\DeleteTicketPriorityUseCase;
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

class DeleteTicketPriorityUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_deletes_priority_inside_transaction(): void
    {
        $id = 123;
        $priority = Mockery::mock();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockPriorityLookup($id, $priority);

        $priority->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $result = (new DeleteTicketPriorityUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Priority deletion failed.'
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        $idGenerator = $this->mockIdValidation($id, false);

        $this->mockAuthorization();

        DB::shouldReceive('transaction')->never();

        $result = (new DeleteTicketPriorityUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid ticket priority ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_priority_does_not_exist(): void
    {
        $id = 123;

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockPriorityLookup($id, null);

        $result = (new DeleteTicketPriorityUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket priority not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_delete_throws_exception(): void
    {
        $id = 123;
        $priority = Mockery::mock();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockPriorityLookup($id, $priority);

        $priority->shouldReceive('delete')
            ->once()
            ->andThrow(new RuntimeException('Simulated failure.'));

        $result = (new DeleteTicketPriorityUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Failed to delete ticket priority:',
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
            ->with('ticket.priority.delete')
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

    private function mockPriorityLookup(
        int $id,
        mixed $priority
    ): void {
        $model = Mockery::mock(
            'alias:App\Models\TicketPriorityModel'
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
            ->andReturn($priority);
    }
}