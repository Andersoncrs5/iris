<?php

namespace App\Services\Ticket;

use App\Models\TicketModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteOwnTicketUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('ticket.own.delete');

        $userId = Auth::id();

        if ($userId === null) {
            return Result::fail('Authentication is required.');
        }

        try {
            return DB::transaction(function () use ($id, $userId): Result {
                $ticket = TicketModel::query()
                    ->whereKey($id)
                    ->where('user_id', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($ticket === null) {
                    return Result::fail(
                        'Ticket not found or you do not have permission to delete it.'
                    );
                }

                $ticket->delete();

                return Result::ok($ticket);
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'Unable to delete your ticket due to a database error.'
            );
        }
    }
}