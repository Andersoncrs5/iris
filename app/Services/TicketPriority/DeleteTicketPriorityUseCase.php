<?php

namespace App\Services\TicketPriority;

use App\Models\TicketPriorityModel;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\Gate;

class DeleteTicketPriorityUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('ticket.priority.delete');

        $ticket = TicketPriorityModel::find($id);


        if ($ticket === null) {
            return Result::fail("Ticket priority not found");
        }

        try {
            $ticket->delete();
            return Result::ok();
        } catch (\Throwable $th) {
            return Result::fail("Failed to delete ticket priority: " . $th->getMessage());
        }
    }
}
