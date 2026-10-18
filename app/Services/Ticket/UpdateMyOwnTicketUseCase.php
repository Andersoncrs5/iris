<?php

namespace App\Services\Ticket;

use App\DTOs\Ticket\UpdateMyOwnTicketDTO;
use App\Models\TicketModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateMyOwnTicketUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(
        UpdateMyOwnTicketDTO $dto,
        int $id
    ): Result {
        Gate::authorize('ticket.own.update');

        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket ID.', 400);
        }

        $userId = Auth::id();

        if ($userId === null) {
            return Result::fail('Authentication is required.');
        }

        try {
            return DB::transaction(function () use (
                $dto,
                $id,
                $userId
            ): Result {
                $ticket = TicketModel::query()
                    ->whereKey($id)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($ticket === null) {
                    return Result::fail(
                        'Ticket not found or you do not have permission to update it.'
                    );
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
                'An unexpected database error occurred while updating your ticket.'
            );
        }
    }
}