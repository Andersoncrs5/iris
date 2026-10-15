<?php

namespace App\Services\Ticket;

use App\Models\TicketModel;
use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CloseTicketUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('ticket.close');

        try {
            return DB::transaction(function () use ($id): Result {
                $ticket = TicketModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if (!$ticket) {
                    return Result::fail('Ticket not found.');
                }

                $currentStatus = TicketStatusModel::query()
                    ->find($ticket->status_id);

                $currentStatusCode = strtoupper(
                    (string) ($currentStatus?->code ?? '')
                );

                if (
                    $ticket->closed_at !== null
                    || $currentStatusCode === 'CLOSED'
                ) {
                    return Result::fail(
                        'This ticket is already closed.'
                    );
                }

                $isResolved = $ticket->resolved_at !== null
                    || $currentStatusCode === 'RESOLVED';

                if (!$isResolved) {
                    return Result::fail(
                        'Only resolved tickets can be closed.'
                    );
                }

                $closedStatus = TicketStatusModel::query()
                    ->where('is_active', true)
                    ->whereRaw('UPPER(code) = ?', ['CLOSED'])
                    ->first();

                if (!$closedStatus) {
                    return Result::fail(
                        'No active CLOSED status is configured.'
                    );
                }

                $ticket->status_id = $closedStatus->id;
                $ticket->closed_at = now();
                $ticket->last_activity_at = now();
                $ticket->version = (int) $ticket->version + 1;

                $ticket->save();

                $ticket->refresh();

                $ticket->load([
                    'user',
                    'agent',
                    'category',
                    'status',
                    'priority',
                ]);

                return Result::ok($ticket);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'Unable to close the ticket due to a database error.'
            );
        }
    }
}