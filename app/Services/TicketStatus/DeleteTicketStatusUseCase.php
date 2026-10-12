<?php

namespace App\Services\TicketStatus;

use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use Illuminate\Support\Facades\Gate;

class DeleteTicketStatusUseCase
{
    public function execute(int $id): Result
    {
        Gate::authorize('ticket.status.delete');

        $ticket = TicketStatusModel::find($id);


        if ($ticket === null) {
            return Result::fail("Tag not found");
        }

        try {
            $ticket->delete();
            return Result::ok();
        } catch (\Throwable $th) {
            return Result::fail("Failed to delete ticket status: " . $th->getMessage());
        }
    }
}
