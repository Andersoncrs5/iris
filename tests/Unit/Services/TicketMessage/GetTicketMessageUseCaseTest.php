<?php

namespace Tests\Unit\Services\TicketMessage;

use App\Services\TicketMessage\GetTicketMessageUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class GetTicketMessageUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_message_ticket_and_paginated_replies(): void
    {
        $id = 123;
        $ticket = new \stdClass();
        $user = Mockery::mock();

        $message = $this->makeMessage(
            ticket: $ticket,
            id: $id,
            ticketId: 456,
        );

        $paginator = Mockery::mock();

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $user->shouldReceive('can')
            ->once()
            ->with('ticket.message.internal.view')
            ->andReturn(false);

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTicketAuthorization($user, $ticket);

        $messageQuery = Mockery::mock();
        $repliesQuery = Mockery::mock();

        $model = Mockery::mock('alias:App\Models\TicketMessageModel');

        $model->shouldReceive('query')
            ->twice()
            ->andReturn($messageQuery, $repliesQuery);

        $messageQuery->shouldReceive('with')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();

        $messageQuery->shouldReceive('findOrFail')
            ->once()
            ->with($id)
            ->andReturn($message);

        $repliesQuery->shouldReceive('where')
            ->times(3)
            ->andReturnSelf();

        $repliesQuery->shouldReceive('with')
            ->once()
            ->with('user')
            ->andReturnSelf();

        $repliesQuery->shouldReceive('orderBy')
            ->twice()
            ->andReturnSelf();

        $repliesQuery->shouldReceive('paginate')
            ->once()
            ->with(50, ['*'], 'replies_page')
            ->andReturn($paginator);

        $paginator->shouldReceive('withQueryString')
            ->once()
            ->andReturnSelf();

        $result = (new GetTicketMessageUseCase($idGenerator))
            ->execute($id, 100);

        $this->assertSame($message, $result['ticketMessage']);
        $this->assertSame($ticket, $result['ticket']);
        $this->assertSame($paginator, $result['replies']);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_requires_authentication(): void
    {
        Auth::shouldReceive('user')
            ->once()
            ->andReturn(null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldNotReceive('isValid');

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage(
            'Authentication is required to view ticket messages.'
        );

        (new GetTicketMessageUseCase($idGenerator))->execute(123);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_message_id(): void
    {
        $user = Mockery::mock();

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $idGenerator = $this->mockIdValidation(0, false);

        $user->shouldNotReceive('can');

        $this->expectException(ModelNotFoundException::class);

        (new GetTicketMessageUseCase($idGenerator))->execute(0);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_hides_internal_message_from_unauthorized_user(): void
    {
        $id = 123;
        $ticket = new \stdClass();
        $user = Mockery::mock();

        $message = $this->makeMessage(
            ticket: $ticket,
            id: $id,
            ticketId: 456,
            isInternal: true,
        );

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $user->shouldReceive('can')
            ->once()
            ->with('ticket.message.internal.view')
            ->andReturn(false);

        $idGenerator = $this->mockIdValidation($id, true);

        $this->mockTicketAuthorization($user, $ticket);

        $query = Mockery::mock();

        $model = Mockery::mock('alias:App\Models\TicketMessageModel');

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('with')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();

        $query->shouldReceive('findOrFail')
            ->once()
            ->with($id)
            ->andReturn($message);

        $this->expectException(NotFoundHttpException::class);

        (new GetTicketMessageUseCase($idGenerator))->execute($id);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_hides_message_when_ticket_does_not_exist(): void
    {
        $id = 123;
        $user = Mockery::mock();

        $message = $this->makeMessage(
            ticket: null,
            id: $id,
            ticketId: 456,
        );

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $user->shouldReceive('can')
            ->once()
            ->with('ticket.message.internal.view')
            ->andReturn(false);

        $idGenerator = $this->mockIdValidation($id, true);

        $query = Mockery::mock();

        $model = Mockery::mock('alias:App\Models\TicketMessageModel');

        $model->shouldReceive('query')
            ->once()
            ->andReturn($query);

        $query->shouldReceive('with')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();

        $query->shouldReceive('findOrFail')
            ->once()
            ->with($id)
            ->andReturn($message);

        $this->expectException(NotFoundHttpException::class);

        (new GetTicketMessageUseCase($idGenerator))->execute($id);
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

    private function mockTicketAuthorization(
        mixed $user,
        mixed $ticket,
    ): void {
        $userGate = Mockery::mock();

        Gate::shouldReceive('forUser')
            ->once()
            ->with($user)
            ->andReturn($userGate);

        $userGate->shouldReceive('authorize')
            ->once()
            ->with('view', $ticket)
            ->andReturn(null);
    }

    private function makeMessage(
        mixed $ticket,
        int $id,
        int $ticketId,
        bool $isInternal = false,
        ?int $parentId = null,
    ): object {
        return new class(
            $ticket,
            $id,
            $ticketId,
            $isInternal,
            $parentId,
        ) {
            public function __construct(
                public mixed $ticket,
                private readonly int $id,
                public int $ticket_id,
                public bool $is_internal = false,
                public ?int $parent_id = null,
            ) {}

            public function getKey(): int
            {
                return $this->id;
            }
        };
    }
}