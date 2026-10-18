<?php

namespace Tests\Unit\Services\Ticket;

use App\DTOs\Ticket\CreateTicketDTO;
use App\Services\Ticket\CreateTicketUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PDOException;
use Tests\TestCase;

class CreateTicketUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_creates_ticket_inside_transaction(): void
    {
        $this->installModelAliases();

        $dto = $this->makeDto();

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockCategoryLookup((object) ['id' => 10]);
        $this->mockPriorityLookup((object) ['id' => 20]);
        $this->mockInitialStatuses([
            (object) ['id' => 30],
        ]);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(9001);

        $result = (new CreateTicketUseCase($idGenerator))
            ->execute($dto);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket creation failed.'
        );

        $ticket = $result->getValue();

        $this->assertInstanceOf(
            \App\Models\TicketModel::class,
            $ticket
        );

        $this->assertSame(9001, $ticket->id);
        $this->assertSame($dto->userId, $ticket->user_id);
        $this->assertSame($dto->categoryId, $ticket->category_id);
        $this->assertSame($dto->priorityId, $ticket->priority_id);
        $this->assertSame($dto->subject, $ticket->subject);
        $this->assertSame($dto->description, $ticket->description);
        $this->assertSame($dto->source, $ticket->source);
        $this->assertSame(30, $ticket->status_id);
        $this->assertNull($ticket->agent_id);
        $this->assertSame(1, $ticket->version);
        $this->assertSame(1, $ticket->saveCalls);
        $this->assertTrue($ticket->wasRefreshed);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_category_does_not_exist_or_is_inactive(): void
    {
        $this->installModelAliases();

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockCategoryLookup(null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldNotReceive('generate');

        $result = (new CreateTicketUseCase($idGenerator))
            ->execute($this->makeDto());

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'The selected category does not exist or is inactive.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_priority_does_not_exist_or_is_inactive(): void
    {
        $this->installModelAliases();

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockCategoryLookup((object) ['id' => 10]);
        $this->mockPriorityLookup(null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldNotReceive('generate');

        $result = (new CreateTicketUseCase($idGenerator))
            ->execute($this->makeDto());

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'The selected priority does not exist or is inactive.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_no_active_initial_status_exists(): void
    {
        $this->installModelAliases();

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockCategoryLookup((object) ['id' => 10]);
        $this->mockPriorityLookup((object) ['id' => 20]);
        $this->mockInitialStatuses([]);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldNotReceive('generate');

        $result = (new CreateTicketUseCase($idGenerator))
            ->execute($this->makeDto());

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'No active initial ticket status is configured.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_fails_when_multiple_active_initial_statuses_exist(): void
    {
        $this->installModelAliases();

        $this->mockAuthorization();
        $this->mockTransaction();
        $this->mockCategoryLookup((object) ['id' => 10]);
        $this->mockPriorityLookup((object) ['id' => 20]);

        $this->mockInitialStatuses([
            (object) ['id' => 30],
            (object) ['id' => 31],
        ]);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);
        $idGenerator->shouldNotReceive('generate');

        $result = (new CreateTicketUseCase($idGenerator))
            ->execute($this->makeDto());

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'Multiple active initial ticket statuses are configured.',
            $result->getError() ?? ''
        );
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_returns_failure_when_database_exception_occurs(): void
    {
        $this->mockAuthorization();

        $exception = new QueryException(
            'pgsql',
            'insert into tickets',
            [],
            new PDOException('Database failure.')
        );

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class))
            ->andThrow($exception);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $result = (new CreateTicketUseCase($idGenerator))
            ->execute($this->makeDto());

        $this->assertTrue($result->isFailure());

        $this->assertStringContainsString(
            'An unexpected database error occurred while creating the ticket.',
            $result->getError() ?? ''
        );
    }

    private function makeDto(): CreateTicketDTO
    {
        return new CreateTicketDTO(
            userId: 100,
            agentId: null,
            teamId: 5,
            categoryId: 10,
            priorityId: 20,
            subject: 'Cannot access my account',
            description: 'Login fails when I enter my credentials.',
            source: 'WEB',
        );
    }

    private function installModelAliases(): void
    {
        class_alias(
            CreateTicketUseCaseTestCategoryAlias::class,
            \App\Models\CategoryModel::class
        );

        class_alias(
            CreateTicketUseCaseTestPriorityAlias::class,
            \App\Models\TicketPriorityModel::class
        );

        class_alias(
            CreateTicketUseCaseTestStatusAlias::class,
            \App\Models\TicketStatusModel::class
        );

        class_alias(
            CreateTicketUseCaseTestTicketAlias::class,
            \App\Models\TicketModel::class
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.create')
            ->andReturn(null);
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

    private function mockCategoryLookup(mixed $category): void
    {
        $query = Mockery::mock();

        CreateTicketUseCaseTestCategoryAlias::$queryBuilder = $query;

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('first')
            ->once()
            ->andReturn($category);
    }

    private function mockPriorityLookup(mixed $priority): void
    {
        $query = Mockery::mock();

        CreateTicketUseCaseTestPriorityAlias::$queryBuilder = $query;

        $query->shouldReceive('whereKey')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('first')
            ->once()
            ->andReturn($priority);
    }

    private function mockInitialStatuses(array $statuses): void
    {
        $query = Mockery::mock();

        CreateTicketUseCaseTestStatusAlias::$queryBuilder = $query;

        $query->shouldReceive('where')
            ->twice()
            ->andReturnSelf();

        $query->shouldReceive('orderBy')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('get')
            ->once()
            ->andReturn(new EloquentCollection($statuses));
    }
}

/*
|--------------------------------------------------------------------------
| Model aliases usados exclusivamente pelos testes
|--------------------------------------------------------------------------
*/

class CreateTicketUseCaseTestCategoryAlias
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

class CreateTicketUseCaseTestPriorityAlias
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

class CreateTicketUseCaseTestStatusAlias
{
    public static mixed $queryBuilder = null;

    public static function query(): mixed
    {
        return self::$queryBuilder;
    }
}

class CreateTicketUseCaseTestTicketAlias
{
    public mixed $id = null;
    public mixed $user_id = null;
    public mixed $agent_id = null;
    public mixed $team_id = null;
    public mixed $category_id = null;
    public mixed $priority_id = null;
    public mixed $subject = null;
    public mixed $description = null;
    public mixed $source = null;
    public mixed $status_id = null;
    public mixed $version = null;

    public int $saveCalls = 0;
    public bool $wasRefreshed = false;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    public function save(): bool
    {
        $this->saveCalls++;

        return true;
    }

    public function refresh(): self
    {
        $this->wasRefreshed = true;

        return $this;
    }
}