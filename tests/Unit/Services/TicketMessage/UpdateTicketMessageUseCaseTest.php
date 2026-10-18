<?php

namespace Tests\Unit\Services\TicketMessage;

use App\DTOs\TicketMessage\UpdateTicketMessageDTO;
use App\Services\TicketMessage\UpdateTicketMessageUseCase;
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

class UpdateTicketMessageUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_updates_message_inside_transaction(): void
    {
        $id = 123;
        $dto = $this->makeDto();
        $message = $this->makeMessage();

        $idGenerator = $this->mockIdValidation($id, true);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $id);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $message);

        $this->mockTransaction();
        $this->mockMessageLookup($id, $message);

        $result = (new UpdateTicketMessageUseCase($idGenerator))
            ->execute($dto, $id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Message update failed.'
        );

        $this->assertSame($message, $result->getValue());
        $this->assertSame('Updated message content', $message->message);
        $this->assertTrue($message->is_edited);
        $this->assertNotNull($message->edited_at);
        $this->assertSame(4, $message->version);
        $this->assertSame(1, $message->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $id);

        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new UpdateTicketMessageUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid ticket message ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_message_does_not_exist(): void
    {
        $id = 123;

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $id);

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockMessageLookup($id, null);

        $result = (new UpdateTicketMessageUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Message not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $id = 123;

        Gate::shouldReceive('authorize')
            ->once()
            ->with('update', $id);

        $idGenerator = $this->mockIdValidation($id, true);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'update ticket_messages',
                    [],
                    new PDOException('Database failure.')
                )
            );

        $result = (new UpdateTicketMessageUseCase($idGenerator))
            ->execute($this->makeDto(), $id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while updating the message.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(): UpdateTicketMessageDTO
    {
        return new UpdateTicketMessageDTO(
            message: 'Updated message content',
        );
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

    private function mockMessageLookup(int $id, mixed $message): void
    {
        $model = Mockery::mock(
            'alias:App\Models\TicketMessageModel'
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
            ->andReturn($message);
    }

    private function makeMessage(): object
    {
        return new class {
            public string $message = 'Original message';
            public bool $is_edited = false;
            public mixed $edited_at = null;
            public int $version = 3;
            public int $saveCalls = 0;

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
}