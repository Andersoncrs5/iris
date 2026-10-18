<?php

namespace Tests\Unit\Services\Ticket;

use App\DTOs\Ticket\UpdateMyOwnTicketDTO;
use App\Services\Ticket\UpdateMyOwnTicketUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PDOException;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class UpdateMyOwnTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_updates_own_ticket_inside_transaction(): void
    {
        $id = 123;
        $userId = 100;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockAuthenticatedUser($userId);
        $this->mockTransaction();

        $dto = $this->makeDto();
        $ticket = $this->makeTicket(version: $dto->version);

        $this->mockTicketLookup($ticket);

        $result = (new UpdateMyOwnTicketUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket update failed.'
        );

        $this->assertSame($ticket, $result->getValue());
        $this->assertSame('Updated subject', $ticket->subject);
        $this->assertSame('Updated description', $ticket->description);
        $this->assertSame('WEB', $ticket->source);
        $this->assertSame(5, $ticket->satisfaction_rating);
        $this->assertSame('Great service', $ticket->satisfaction_comment);
        $this->assertTrue($ticket->is_escalated);
        $this->assertSame(4, $ticket->version);
        $this->assertSame(1, $ticket->saveCalls);
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
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, false);

        Auth::shouldReceive('id')->never();
        DB::shouldReceive('transaction')->never();

        $result = (new UpdateMyOwnTicketUseCase($idGenerator))
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
    public function test_it_fails_when_user_is_not_authenticated(): void
    {
        $id = 123;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockAuthenticatedUser(null);

        DB::shouldReceive('transaction')->never();

        $result = (new UpdateMyOwnTicketUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

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
        $id = 123;
        $userId = 100;

        $this->mockAuthorization();

        $idGenerator = $this->mockIdValidation($id, true);
        $this->mockAuthenticatedUser($userId);
        $this->mockTransaction();
        $this->mockTicketLookup(null);

        $result = (new UpdateMyOwnTicketUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Ticket not found or you do not have permission to update it.',
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
        $this->mockAuthenticatedUser(100);
        $this->mockTransaction();

        $dto = $this->makeDto(version: 3);
        $ticket = $this->makeTicket(version: 4);

        $this->mockTicketLookup($ticket);

        $result = (new UpdateMyOwnTicketUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'This ticket has been modified by another user.',
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
        $this->mockAuthenticatedUser(100);

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

        $result = (new UpdateMyOwnTicketUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while updating your ticket.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(int $version = 3): UpdateMyOwnTicketDTO
    {
        return UpdateMyOwnTicketDTO::fromArray([
            'version' => $version,
            'subject' => ' Updated subject ',
            'description' => ' Updated description ',
            'source' => ' web ',
            'satisfaction_rating' => 5,
            'satisfaction_comment' => ' Great service ',
            'is_escalated' => true,
        ]);
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.own.update')
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
                static fn (Closure $callback) => $callback()
            );
    }

    private function mockTicketLookup(mixed $ticket): void
    {
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

    private function makeTicket(int $version = 3): object
    {
        return new class($version) {
            public mixed $subject = null;
            public mixed $description = null;
            public mixed $source = null;
            public mixed $satisfaction_rating = null;
            public mixed $satisfaction_comment = null;
            public mixed $is_escalated = null;

            public int $saveCalls = 0;
            public bool $refreshed = false;
            public array $loadedRelations = [];

            public function __construct(
                public int $version,
            ) {}

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