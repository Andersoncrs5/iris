<?php

namespace Tests\Unit\Services\TicketMessage;

use App\DTOs\TicketMessage\CreateTicketMessageDTO;
use App\Services\TicketMessage\CreateTicketMessageUseCase;
use App\Utils\Enums\TicketMessage\TicketMessageTypeEnum;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class CreateTicketMessageUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_creates_ticket_message_successfully(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();

        $ticket = new CreateTicketMessageTestTicket(500);

        $this->mockAuthorization($ticket);
        $this->mockIdValidation($dto->ticketId, true);
        $this->mockAuthenticatedUser(100);
        $this->mockTransaction();
        $this->mockTicketLookup($dto->ticketId, $ticket);

        $idGenerator = $this->makeIdGenerator(
            ticketId: $dto->ticketId,
            valid: true,
            generatedId: 9001,
        );

        $result = (new CreateTicketMessageUseCase($idGenerator))
            ->execute($dto);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket message creation failed.'
        );

        $message = $result->getValue();

        $this->assertInstanceOf(
            \App\Models\TicketMessageModel::class,
            $message
        );

        $this->assertSame(9001, $message->id);
        $this->assertSame(500, $message->ticket_id);
        $this->assertSame(100, $message->user_id);
        $this->assertSame(trim($dto->message), $message->message);
        $this->assertSame($dto->type->value, $message->type);
        $this->assertSame($dto->isInternal, $message->is_internal);
        $this->assertFalse($message->is_edited);
        $this->assertNull($message->edited_at);
        $this->assertSame($dto->attachments, $message->attachments);
        $this->assertSame(1, $message->version);
        $this->assertNull($message->parent_id);
        $this->assertTrue($message->saved);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_ticket_id_without_opening_transaction(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto(ticketId: -1);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.message.create');

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with(-1)
            ->andReturn(false);

        $idGenerator->shouldNotReceive('generate');

        DB::shouldReceive('transaction')->never();

        $result = (new CreateTicketMessageUseCase($idGenerator))
            ->execute($dto);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid ticket ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_invalid_parent_id_without_opening_transaction(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto(parentId: -2);

        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.message.create');

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with($dto->ticketId)
            ->andReturn(true);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with(-2)
            ->andReturn(false);

        $idGenerator->shouldNotReceive('generate');

        DB::shouldReceive('transaction')->never();

        $result = (new CreateTicketMessageUseCase($idGenerator))
            ->execute($dto);

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Invalid parent message ID.',
            $result->getError() ?? ''
        );

        $this->assertSame(400, $result->getCode());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_rejects_system_event_messages(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto(
            type: TicketMessageTypeEnum::SYSTEM_EVENT,
        );

        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.message.create');

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with($dto->ticketId)
            ->andReturn(true);

        $idGenerator->shouldNotReceive('generate');

        DB::shouldReceive('transaction')->never();

        try {
            (new CreateTicketMessageUseCase($idGenerator))->execute($dto);
            $this->fail('Expected validation exception was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString(
                'System events cannot be created by users.',
                $exception->errors()['type'][0] ?? ''
            );
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_requires_authentication(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();

        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.message.create');

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with($dto->ticketId)
            ->andReturn(true);

        Auth::shouldReceive('id')
            ->once()
            ->andReturn(null);

        DB::shouldReceive('transaction')->never();

        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage(
            'Authentication is required to create a ticket message.'
        );

        (new CreateTicketMessageUseCase($idGenerator))->execute($dto);
    }

    private function makeDto(
        int $ticketId = 123,
        ?int $parentId = null,
        ?TicketMessageTypeEnum $type = null,
        bool $isInternal = false,
    ): CreateTicketMessageDTO {
        $type ??= $this->userMessageType();

        return new CreateTicketMessageDTO(
            ticketId: $ticketId,
            message: '  Hello, ticket support!  ',
            type: $type,
            isInternal: $isInternal,
            attachments: [],
            parentId: $parentId,
        );
    }

    private function userMessageType(): TicketMessageTypeEnum
    {
        foreach (TicketMessageTypeEnum::cases() as $case) {
            if ($case !== TicketMessageTypeEnum::SYSTEM_EVENT) {
                return $case;
            }
        }

        throw new \LogicException(
            'TicketMessageTypeEnum must define a non-system message type.'
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            CreateTicketMessageTestTicketModelAlias::class,
            \App\Models\TicketModel::class
        );

        class_alias(
            CreateTicketMessageTestMessageModelAlias::class,
            \App\Models\TicketMessageModel::class
        );
    }

    private function mockAuthorization(mixed $ticket): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.message.create');

        Gate::shouldReceive('authorize')
            ->once()
            ->with('view', $ticket);
    }

    private function mockIdValidation(
        int $ticketId,
        bool $valid,
    ): void {
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->with($ticketId)
            ->andReturn($valid);
    }

    private function makeIdGenerator(
        int $ticketId,
        bool $valid,
        int $generatedId,
    ): IdGeneratorInterface {
        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('isValid')
            ->once()
            ->with($ticketId)
            ->andReturn($valid);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn($generatedId);

        return $idGenerator;
    }

    private function mockAuthenticatedUser(?int $userId): void
    {
        Auth::shouldReceive('id')
            ->once()
            ->andReturn($userId);
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

    private function mockTicketLookup(int $id, mixed $ticket): void
    {
        $query = Mockery::mock();

        CreateTicketMessageTestTicketModelAlias::$queryBuilder = $query;

        $query->shouldReceive('findOrFail')
            ->once()
            ->with($id)
            ->andReturn($ticket);
    }
}

/*
|--------------------------------------------------------------------------
| Modelos falsos exclusivos deste arquivo de testes
|--------------------------------------------------------------------------
*/

class CreateTicketMessageTestTicketModelAlias
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

class CreateTicketMessageTestMessageModelAlias
{
    public static mixed $queryBuilder = null;

    public mixed $id = null;
    public mixed $ticket_id = null;
    public mixed $user_id = null;
    public mixed $message = null;
    public mixed $type = null;
    public mixed $is_internal = null;
    public mixed $is_edited = null;
    public mixed $edited_at = null;
    public mixed $attachments = null;
    public mixed $version = null;
    public mixed $parent_id = null;

    public bool $saved = false;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function save(): bool
    {
        $this->saved = true;

        return true;
    }
}

class CreateTicketMessageTestTicket
{
    public function __construct(
        private readonly int $id,
    ) {}

    public function getKey(): int
    {
        return $this->id;
    }
}