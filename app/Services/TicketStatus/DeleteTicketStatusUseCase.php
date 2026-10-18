<?php

namespace App\Services\TicketStatus;

use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class DeleteTicketStatusUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(int $id): Result
    {
        Gate::authorize('ticket.status.delete');

        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket status ID.', 400);
        }

        try {
            return DB::transaction(function () use ($id): Result {
                $status = TicketStatusModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($status === null) {
                    return Result::fail('Ticket status not found.');
                }

                $status->delete();

                return Result::ok();
            }, 3);
        } catch (Throwable $th) {
            report($th);

            return Result::fail(
                'Failed to delete ticket status: ' . $th->getMessage()
            );
        }
    }
}