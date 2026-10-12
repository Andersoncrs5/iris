<?php

namespace App\Services\TicketPriority;

use App\DTOs\TicketPriority\CreateTicketPriorityDTO;
use App\Models\TicketPriorityModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateTicketPriorityUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(CreateTicketPriorityDTO $dto): Result
    {
        Gate::authorize('ticket.priority.create');

        return DB::transaction(function () use ($dto): Result {
            if ($dto->isDefault) {
                $defaultPriorities = TicketPriorityModel::withTrashed()
                    ->where('is_default', true)
                    ->lockForUpdate()
                    ->get();

                foreach ($defaultPriorities as $defaultPriority) {
                    $defaultPriority->is_default = false;
                    $defaultPriority->version++;
                    $defaultPriority->save();
                }
            }

            $priority = new TicketPriorityModel([
                'name' => $dto->name,
                'code' => $dto->code,
                'level' => $dto->level,
                'description' => $dto->description,
                'color' => $dto->color,
                'icon' => $dto->icon,
                'response_time_minutes' => $dto->responseTimeMinutes,
                'resolution_time_minutes' => $dto->resolutionTimeMinutes,
                'escalation_time_minutes' => $dto->escalationTimeMinutes,
                'is_default' => $dto->isDefault,
                'is_active' => $dto->isActive,
            ]);

            $priority->id = $this->idGenerator->generate();
            $priority->save();

            return Result::ok($priority);
        });
    }
}