<?php

namespace App\Services\TicketPriority;

use App\Models\TicketPriorityModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DeleteTicketPriorityUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(int $id): Result
    {
        Gate::authorize('ticket.priority.delete');

        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket priority ID.', 400);
        }

        try {
            return DB::transaction(function () use ($id): Result {
                $priority = TicketPriorityModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($priority === null) {
                    return Result::fail('Ticket priority not found.');
                }

                $priority->delete();

                return Result::ok();
            }, 3);
        } catch (Throwable $th) {
            report($th);

            return Result::fail(
                'Failed to delete ticket priority: ' . $th->getMessage()
            );
        }
    }
}