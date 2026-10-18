<?php

namespace App\Services\Ticket;

use App\DTOs\Ticket\UpdateTicketDTO;
use App\Models\TicketModel;
use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateTicketUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(
        UpdateTicketDTO $dto,
        int $id
    ): Result {
        Gate::authorize('ticket.update');

        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket ID.', 400);
        }

        try {
            return DB::transaction(function () use ($dto, $id): Result {
                $ticket = TicketModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($ticket === null) {
                    return Result::fail('Ticket not found.');
                }

                if ((int) $ticket->version !== $dto->version) {
                    return Result::fail(
                        'This ticket has been modified by another user. Refresh the page and try again.'
                    );
                }

                $attributes = $dto->toArray();

                if ($attributes === []) {
                    return Result::fail(
                        'No ticket fields were provided for updating.'
                    );
                }

                if (array_key_exists('status_id', $attributes)) {
                    $statusExists = TicketStatusModel::query()
                        ->whereKey($attributes['status_id'])
                        ->whereNull('deleted_at')
                        ->where('is_active', true)
                        ->exists();

                    if (!$statusExists) {
                        return Result::fail(
                            'The selected status does not exist or is inactive.'
                        );
                    }
                }

                $ticket->fill($attributes);
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
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the ticket.'
            );
        }
    }
}