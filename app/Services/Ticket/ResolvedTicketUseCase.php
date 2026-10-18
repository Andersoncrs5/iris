<?php

namespace App\Services\Ticket;

use App\Models\TicketModel;
use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ResolvedTicketUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(int $id): Result
    {
        Gate::authorize('ticket.resolve');

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

                if (
                    $ticket->closed_at !== null
                    || $currentStatusCode === 'CLOSED'
                ) {
                    return Result::fail(
                        'A closed ticket cannot be resolved.'
                    );
                }

                if (
                    $ticket->resolved_at !== null
                    || $currentStatusCode === 'RESOLVED'
                ) {
                    return Result::fail(
                        'This ticket is already resolved.'
                    );
                }

                $resolvedStatus = TicketStatusModel::query()
                    ->where('is_active', true)
                    ->whereRaw('UPPER(code) = ?', ['RESOLVED'])
                    ->first();

                if (!$resolvedStatus) {
                    return Result::fail(
                        'No active RESOLVED status is configured.'
                    );
                }

                $now = now();

                $ticket->status_id = $resolvedStatus->id;
                $ticket->resolved_at = $now;
                $ticket->last_activity_at = $now;
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
                'Unable to resolve the ticket due to a database error.'
            );
        }
    }
}