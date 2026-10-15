<?php

namespace App\Services\Ticket;

use App\Models\TicketModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteTicketUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('ticket.delete');

        try {
            return DB::transaction(function () use ($id): Result {
                $ticket = TicketModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if (!$ticket) {
                    return Result::fail('Ticket not found.');
                }

                $ticket->delete();

                return Result::ok($ticket);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'Unable to delete the ticket due to a database error.'
            );
        }
    }
}