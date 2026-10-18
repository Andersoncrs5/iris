<?php

namespace Tests\Unit\Services\TicketPriority;

use App\DTOs\TicketPriority\UpdateTicketPriorityDTO;
use App\Services\TicketPriority\UpdateTicketPriorityUseCase;
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

class UpdateTicketPriorityUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_updates_priority_inside_transaction(): void
    {
        $id = 123;
        $dto = $this->makeDto();
        $priority = $this->makePriority($id, $dto->version);

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();
        $this->mockPriorityLookup($id, $priority);

        $result = (new UpdateTicketPriorityUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Priority update failed.'
        );

        $this->assertSame($priority, $result->getValue());
        $this->assertSame('Urgent', $priority->name);
        $this->assertSame('URGENT', $priority->code);
        $this->assertSame(1, $priority->level);
        $this->assertSame('Urgent priority', $priority->description);
        $this->assertSame('#FF0000', $priority->color);
        $this->assertSame('alert-circle', $priority->icon);
        $this->assertSame(15, $priority->response_time_minutes);
        $this->assertSame(60, $priority->resolution_time_minutes);
        $this->assertSame(30, $priority->escalation_time_minutes);
        $this->assertFalse($priority->is_default);
        $this->assertTrue($priority->is_active);
        $this->assertSame(3, $priority->version);
        $this->assertSame(1, $priority->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new UpdateTicketPriorityUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

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

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();
        $this->mockPriorityLookup($id, null);

        $result = (new UpdateTicketPriorityUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket priority not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_version_is_outdated(): void
    {
        $id = 123;
        $dto = $this->makeDto(version: 2);
        $priority = $this->makePriority($id, version: 3);

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();
        $this->mockPriorityLookup($id, $priority);

        $result = (new UpdateTicketPriorityUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'This ticket priority has been modified by another user.',
            $result->getError() ?? ''
        );

        $this->assertSame(0, $priority->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_removes_default_from_other_priorities(): void
    {
        $id = 123;
        $dto = $this->makeDto(isDefault: true);
        $priority = $this->makePriority($id, $dto->version);
        $otherPriority = $this->makePriority(456, version: 5);
        $otherPriority->is_default = true;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $model = $this->mockPriorityLookup($id, $priority);
        $otherDefaultsQuery = Mockery::mock();

        $model->shouldReceive('withTrashed')
            ->once()
            ->andReturn($otherDefaultsQuery);

        $otherDefaultsQuery->shouldReceive('where')
            ->twice()
            ->andReturnSelf();

        $otherDefaultsQuery->shouldReceive('orderBy')
            ->once()
            ->andReturnSelf();

        $otherDefaultsQuery->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $otherDefaultsQuery->shouldReceive('get')
            ->once()
            ->andReturn([$otherPriority]);

        $result = (new UpdateTicketPriorityUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Default priority update failed.'
        );

        $this->assertTrue($priority->is_default);
        $this->assertSame(3, $priority->version);
        $this->assertSame(1, $priority->saveCalls);

        $this->assertFalse($otherPriority->is_default);
        $this->assertSame(6, $otherPriority->version);
        $this->assertSame(1, $otherPriority->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_unique_constraint_is_violated(): void
    {
        $id = 123;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $exception = $this->makeQueryException(
            '23505',
            'Duplicate priority name, code, or level.'
        );

        $this->mockPriorityLookup(
            $id,
            null,
            exception: $exception,
        );

        $result = (new UpdateTicketPriorityUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'A ticket priority with this name, code, or level already exists.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_an_unexpected_database_error_occurs(): void
    {
        $id = 123;

        $this->mockAuthorization();
        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockTransaction();

        $exception = $this->makeQueryException(
            '08006',
            'Database connection failure.'
        );

        $this->mockPriorityLookup(
            $id,
            null,
            exception: $exception,
        );

        $result = (new UpdateTicketPriorityUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while updating the ticket priority.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(
        int $version = 2,
        bool $isDefault = false,
    ): UpdateTicketPriorityDTO {
        return new UpdateTicketPriorityDTO(
            version: $version,
            name: 'Urgent',
            code: 'URGENT',
            level: 1,
            description: 'Urgent priority',
            color: '#FF0000',
            icon: 'alert-circle',
            responseTimeMinutes: 15,
            resolutionTimeMinutes: 60,
            escalationTimeMinutes: 30,
            isDefault: $isDefault,
            isActive: true,
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.priority.update')
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

    private function mockPriorityLookup(
        int $id,
        mixed $priority,
        ?QueryException $exception = null,
    ): mixed {
        $model = Mockery::mock(
            'alias:App\Models\TicketPriorityModel'
        );

        $query = Mockery::mock();

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $first = $query->shouldReceive('first')
            ->once();

        if ($exception !== null) {
            $first->andThrow($exception);
        } else {
            $first->andReturn($priority);
        }

        return $model;
    }

    private function makePriority(
        int $id,
        int $version,
    ): object {
        return new class($id, $version) {
            public mixed $name = null;
            public mixed $code = null;
            public mixed $level = null;
            public mixed $description = null;
            public mixed $color = null;
            public mixed $icon = null;
            public mixed $response_time_minutes = null;
            public mixed $resolution_time_minutes = null;
            public mixed $escalation_time_minutes = null;
            public bool $is_default = false;
            public bool $is_active = true;
            public int $saveCalls = 0;

            public function __construct(
                private readonly int $id,
                public int $version,
            ) {}

            public function getKey(): int
            {
                return $this->id;
            }

            public function fill(array $attributes): self
            {
                foreach ($attributes as $key => $value) {
                    $this->{$key} = $value;
                }

                return $this;
            }

            public function save(): bool
            {
                $this->saveCalls++;

                return true;
            }
        };
    }

    private function makeQueryException(
        string $sqlState,
        string $message,
    ): QueryException {
        $previous = new PDOException($message);

        $exception = new QueryException(
            'pgsql',
            'select * from ticket_priorities where id = ?',
            [],
            $previous
        );

        $exception->errorInfo = [$sqlState, 0, $message];

        return $exception;
    }
}