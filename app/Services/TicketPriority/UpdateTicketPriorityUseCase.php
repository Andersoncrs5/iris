<?php

namespace App\Services\TicketPriority;

use App\DTOs\TicketPriority\UpdateTicketPriorityDTO;
use App\Models\TicketPriorityModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateTicketPriorityUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(
        UpdateTicketPriorityDTO $dto,
        int $id
    ): Result {
        Gate::authorize('ticket.priority.update');

        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket priority ID.', 400);
        }

        try {
            return DB::transaction(function () use ($dto, $id): Result {
                $priority = TicketPriorityModel::query()
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if ($priority === null) {
                    return Result::fail('Ticket priority not found.');
                }

                if ((int) $priority->version !== $dto->version) {
                    return Result::fail(
                        'This ticket priority has been modified by another user. Reload the page and try again.'
                    );
                }

                if ($dto->isDefault) {
                    $otherDefaults = TicketPriorityModel::withTrashed()
                        ->where('is_default', true)
                        ->where('id', '!=', $priority->getKey())
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    foreach ($otherDefaults as $otherPriority) {
                        $otherPriority->is_default = false;
                        $otherPriority->version =
                            (int) $otherPriority->version + 1;

                        $otherPriority->save();
                    }
                }

                $priority->fill([
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

                $priority->version = (int) $priority->version + 1;
                $priority->save();

                return Result::ok($priority);
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23505') {
                return Result::fail(
                    'A ticket priority with this name, code, or level already exists.'
                );
            }

            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the ticket priority.'
            );
        }
    }
}