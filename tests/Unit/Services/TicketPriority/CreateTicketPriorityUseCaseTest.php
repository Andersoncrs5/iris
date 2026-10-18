<?php

namespace Tests\Unit\Services\TicketPriority;

use App\DTOs\TicketPriority\CreateTicketPriorityDTO;
use App\Services\TicketPriority\CreateTicketPriorityUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\TestCase;

class CreateTicketPriorityUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_creates_priority_inside_transaction(): void
    {
        $this->installModelAlias();
        $this->mockAuthorization();
        $this->mockTransaction();

        $dto = $this->makeDto();

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(12345);

        $result = (new CreateTicketPriorityUseCase($idGenerator))
            ->execute($dto);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Priority creation failed.'
        );

        $priority = $result->getValue();

        $this->assertInstanceOf(
            \App\Models\TicketPriorityModel::class,
            $priority
        );

        $this->assertSame(12345, $priority->id);
        $this->assertSame('High', $priority->name);
        $this->assertSame('HIGH', $priority->code);
        $this->assertSame(2, $priority->level);
        $this->assertSame('High priority', $priority->description);
        $this->assertSame('#FF0000', $priority->color);
        $this->assertSame('alert-circle', $priority->icon);
        $this->assertSame(30, $priority->response_time_minutes);
        $this->assertSame(120, $priority->resolution_time_minutes);
        $this->assertSame(60, $priority->escalation_time_minutes);
        $this->assertFalse($priority->is_default);
        $this->assertTrue($priority->is_active);
        $this->assertSame(1, $priority->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_removes_default_from_existing_priorities_when_creating_a_default(): void
    {
        $this->installModelAlias();
        $this->mockAuthorization();
        $this->mockTransaction();

        $existingPriority = new \App\Models\TicketPriorityModel([
            'name' => 'Previous default',
            'code' => 'PREVIOUS',
            'is_default' => true,
            'version' => 2,
        ]);

        $query = Mockery::mock();

        CreateTicketPriorityTestModelAlias::$withTrashedBuilder = $query;

        $query->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('get')
            ->once()
            ->andReturn([$existingPriority]);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(12346);

        $dto = $this->makeDto(isDefault: true);

        $result = (new CreateTicketPriorityUseCase($idGenerator))
            ->execute($dto);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Default priority creation failed.'
        );

        $newPriority = $result->getValue();

        $this->assertSame(12346, $newPriority->id);
        $this->assertTrue($newPriority->is_default);
        $this->assertSame(1, $newPriority->saveCalls);

        $this->assertFalse($existingPriority->is_default);
        $this->assertSame(3, $existingPriority->version);
        $this->assertSame(1, $existingPriority->saveCalls);
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_creates_default_priority_when_no_previous_default_exists(): void
    {
        $this->installModelAlias();
        $this->mockAuthorization();
        $this->mockTransaction();

        $query = Mockery::mock();

        CreateTicketPriorityTestModelAlias::$withTrashedBuilder = $query;

        $query->shouldReceive('where')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('lockForUpdate')
            ->once()
            ->andReturnSelf();

        $query->shouldReceive('get')
            ->once()
            ->andReturn([]);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(12347);

        $result = (new CreateTicketPriorityUseCase($idGenerator))
            ->execute($this->makeDto(isDefault: true));

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Default priority creation failed.'
        );

        $priority = $result->getValue();

        $this->assertSame(12347, $priority->id);
        $this->assertTrue($priority->is_default);
        $this->assertSame(1, $priority->saveCalls);
    }

    private function makeDto(bool $isDefault = false): CreateTicketPriorityDTO
    {
        return new CreateTicketPriorityDTO(
            name: 'High',
            code: 'HIGH',
            level: 2,
            description: 'High priority',
            color: '#FF0000',
            icon: 'alert-circle',
            responseTimeMinutes: 30,
            resolutionTimeMinutes: 120,
            escalationTimeMinutes: 60,
            isDefault: $isDefault,
            isActive: true,
        );
    }

    private function installModelAlias(): void
    {
        class_alias(
            CreateTicketPriorityTestModelAlias::class,
            \App\Models\TicketPriorityModel::class
        );
    }

    private function mockAuthorization(): void
    {
        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.priority.create')
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
}

/**
 * Modelo falso usado exclusivamente nos testes.
 */
class CreateTicketPriorityTestModelAlias
{
    public static mixed $withTrashedBuilder = null;

    public mixed $id = null;
    public mixed $name = null;
    public mixed $code = null;
    public mixed $level = null;
    public mixed $description = null;
    public mixed $color = null;
    public mixed $icon = null;
    public mixed $response_time_minutes = null;
    public mixed $resolution_time_minutes = null;
    public mixed $escalation_time_minutes = null;
    public mixed $is_default = false;
    public mixed $is_active = true;
    public int $version = 1;
    public int $saveCalls = 0;

    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public static function withTrashed(): mixed
    {
        return static::$withTrashedBuilder;
    }

    public function save(): bool
    {
        $this->saveCalls++;

        return true;
    }
}