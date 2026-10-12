<?php 

namespace App\Services\TicketStatus;

use App\Models\TicketStatusModel;
use App\Utils\Result\Result;

class ToggleTicketStatusUseCase
{
    public function execute(int $id): Result
    {
        if ($id == null) {
            return Result::fail("Id should be required", 400);
        }

        $ticket = TicketStatusModel::find($id);

        if (!$ticket) {
            return Result::fail("Ticket Status not found.", 404);
        }

        $ticket->is_active = !$ticket->is_active;
        $ticket->save();

        return Result::ok($ticket);
    }
}