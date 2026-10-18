<?php

namespace Tests\Unit\Services\TicketStatus;

use App\DTOs\TicketStatus\CreateTicketStatusDTO;
use App\Services\TicketStatus\CreateTicketStatusUseCase;
use App\Utils\Snowflake\IdGeneratorInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;
use Tests\TestCase;

class CreateTicketStatusUseCaseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_it_creates_status_inside_transaction(): void
    {
        class_alias(
            CreateTicketStatusTestModelAlias::class,
            \App\Models\TicketStatusModel::class
        );

        $dto = $this->makeDto();

        Gate::shouldReceive('authorize')
            ->once()
            ->with('ticket.status.create')
            ->andReturn(null);

        $idGenerator = Mockery::mock(IdGeneratorInterface::class);

        $idGenerator->shouldReceive('generate')
            ->once()
            ->andReturn(12345);

        DB::shouldReceive('transaction')
            ->once()
            ->with(Mockery::type(Closure::class), 3)
            ->andReturnUsing(
                static fn (Closure $callback, int $attempts) => $callback()
            );

        $result = (new CreateTicketStatusUseCase($idGenerator))
            ->execute($dto);

        $this->assertTrue(
            $result->isSuccess(),
            $result->getError() ?? 'Ticket status creation failed.'
        );

        $status = $result->getValue();

        $this->assertInstanceOf(
            \App\Models\TicketStatusModel::class,
            $status
        );

        $this->assertSame(12345, $status->id);
        $this->assertTrue($status->wasSaved);
        $this->assertSame($status, CreateTicketStatusTestModelAlias::$lastInstance);
    }

    private function makeDto(): CreateTicketStatusDTO
    {
        $reflection = new ReflectionClass(CreateTicketStatusDTO::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $values = [
            'name' => 'Open',
            'code' => 'OPEN',
            'description' => 'Initial ticket status',
            'color' => '#00FF00',
            'icon' => 'circle',
            'displayOrder' => 10,
            'display_order' => 10,
            'isInitial' => true,
            'is_initial' => true,
            'isActive' => true,
            'is_active' => true,
        ];

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $values)) {
                $arguments[] = $values[$name];
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            $type = $parameter->getType();

            if ($type instanceof ReflectionNamedType) {
                if ($type->allowsNull()) {
                    $arguments[] = null;
                    continue;
                }

                $arguments[] = match ($type->getName()) {
                    'string' => 'Test status',
                    'int' => 1,
                    'bool' => true,
                    'float' => 1.0,
                    'array' => [],
                    default => throw new RuntimeException(
                        "Cannot build DTO parameter: {$name}"
                    ),
                };

                continue;
            }

            throw new RuntimeException(
                "Cannot build DTO parameter: {$name}"
            );
        }

        return $reflection->newInstanceArgs($arguments);
    }
}

class CreateTicketStatusTestModelAlias
{
    public static ?self $lastInstance = null;

    private array $attributes = [];

    public bool $wasSaved = false;

    public function __construct(array $attributes = [])
    {
        self::$lastInstance = $this;
        $this->fill($attributes);
    }

    public function fill(array $attributes): self
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    public function save(): bool
    {
        $this->wasSaved = true;

        return true;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        return $this->attributes[$name] ?? null;
    }

    public function __isset(string $name): bool
    {
        return isset($this->attributes[$name]);
    }
}