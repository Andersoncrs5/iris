<?php

namespace Tests\Unit\Services\TicketMessage;

use App\Services\TicketMessage\DeleteTicketMessageUseCase;
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

class DeleteTicketMessageUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_deletes_message_without_replies(): void
    {
        $id = 123;
        $message = Mockery::mock();
        $replies = Mockery::mock();

        $idGenerator = $this->mockIdValidation($id, true);

        $message->shouldReceive('replies')
            ->once()
            ->andReturn($replies);

        $replies->shouldReceive('exists')
            ->once()
            ->andReturn(false);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('delete', $message)
            ->andReturn(null);

        $message->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $this->mockTransaction();
        $this->mockMessageLookup($id, $message);

        $result = (new DeleteTicketMessageUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Message deletion failed.'
        );

        $this->assertSame($message, $result->getValue());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_id_without_opening_transaction(): void
    {
        $id = -1;
        $idGenerator = $this->mockIdValidation($id, false);

        DB::shouldReceive('transaction')->never();

        $result = (new DeleteTicketMessageUseCase($idGenerator))
            ->execute($id);

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
        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockMessageLookup($id, null);

        $result = (new DeleteTicketMessageUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Comment not found.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_message_has_replies(): void
    {
        $id = 123;
        $message = Mockery::mock();
        $replies = Mockery::mock();

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTransaction();
        $this->mockMessageLookup($id, $message);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('delete', $message)
            ->andReturn(null);

        $message->shouldReceive('replies')
            ->once()
            ->andReturn($replies);

        $replies->shouldReceive('exists')
            ->once()
            ->andReturn(true);

        $message->shouldNotReceive('delete');

        $result = (new DeleteTicketMessageUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'This comment cannot be deleted because it has replies.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $id = 123;
        $idGenerator = $this->mockIdValidation($id, true);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andThrow(
                new QueryException(
                    'pgsql',
                    'delete from ticket_messages where id = ?',
                    [$id],
                    new PDOException('Database failure.')
                )
            );

        $result = (new DeleteTicketMessageUseCase($idGenerator))
            ->execute($id);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Unable to delete the comment due to a database error.',
            $result->getError() ?? ''
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
        $model = Mockery::mock('alias:App\Models\TicketMessageModel');
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
}