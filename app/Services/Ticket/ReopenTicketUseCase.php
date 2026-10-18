<?php

namespace App\Services\Ticket;

use App\Models\TicketModel;
use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReopenTicketUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(int $id): Result
    {
        Gate::authorize('ticket.reopen');

        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket ID.', 400);
        }

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

                $canBeReopened =
                    $ticket->resolved_at !== null
                    || $ticket->closed_at !== null
                    || in_array(
                        $currentStatusCode,
                        ['RESOLVED', 'CLOSED'],
                        true
                    );

                if (!$canBeReopened) {
                    return Result::fail(
                        'Only resolved or closed tickets can be reopened.'
                    );
                }

                $targetStatus = TicketStatusModel::query()
                    ->where('is_active', true)
                    ->whereRaw(
                        'UPPER(code) IN (?, ?)',
                        ['REOPENED', 'OPEN']
                    )
                    ->orderByRaw(
                        "CASE WHEN UPPER(code) = 'REOPENED' THEN 0 ELSE 1 END"
                    )
                    ->first();

                if (!$targetStatus) {
                    $targetStatus = TicketStatusModel::query()
                        ->where('is_active', true)
                        ->where('is_initial', true)
                        ->first();
                }

                if (!$targetStatus) {
                    return Result::fail(
                        'No active status is configured for reopening tickets.'
                    );
                }

                $ticket->status_id = $targetStatus->id;
                $ticket->resolved_at = null;
                $ticket->closed_at = null;
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
                'Unable to reopen the ticket due to a database error.'
            );
        }
    }
}