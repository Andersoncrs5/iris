<?php

namespace App\Services\Ticket;

use App\DTOs\Ticket\CreateTicketDTO;
use App\Models\CategoryModel;
use App\Models\TicketModel;
use App\Models\TicketPriorityModel;
use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateTicketUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(CreateTicketDTO $dto): Result
    {
        Gate::authorize('ticket.create');

        try {
            return DB::transaction(function () use ($dto): Result {

                $category = CategoryModel::query()
                    ->whereKey($dto->categoryId)
                    ->where('is_active', true)
                    ->first();

                if ($category === null) {
                    return Result::fail(
                        'The selected category does not exist or is inactive.'
                    );
                }

                $priority = TicketPriorityModel::query()
                    ->whereKey($dto->priorityId)
                    ->where('is_active', true)
                    ->first();

                if ($priority === null) {
                    return Result::fail(
                        'The selected priority does not exist or is inactive.'
                    );
                }

                $initialStatuses = TicketStatusModel::query()
                    ->where('is_initial', true)
                    ->where('is_active', true)
                    ->orderBy('display_order')
                    ->lockForUpdate()
                    ->get();

                if ($initialStatuses->isEmpty()) {
                    return Result::fail(
                        'No active initial ticket status is configured.'
                    );
                }

                if ($initialStatuses->count() > 1) {
                    return Result::fail(
                        'Multiple active initial ticket statuses are configured.'
                    );
                }

                $initialStatus = $initialStatuses->first();

                $ticket = new TicketModel($dto->toArray());

                $ticket->id = $this->idGenerator->generate();
                $ticket->status_id = $initialStatus->id;
                $ticket->agent_id = null;
                $ticket->version = 1;

                $ticket->save();

                $ticket->refresh();

                return Result::ok($ticket);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while creating the ticket.'
            );
        }
    }
}
