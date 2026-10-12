<?php

namespace App\Services\TicketStatus;

use App\DTOs\TicketStatus\UpdateTicketStatusDTO;
use App\Models\TicketStatusModel;
use App\Utils\Result\Result;

class UpdateTicketStatusUseCase
{
    public function execute(UpdateTicketStatusDTO $dto, int|string $id): Result
    {
        $ticketStatus = TicketStatusModel::find($id);

        if (!$ticketStatus) {
            return Result::fail("Ticket status not found.", 404);
        }

        $ticketStatus->name = $dto->name;
        $ticketStatus->code = $dto->code;
        $ticketStatus->description = $dto->description;
        $ticketStatus->color = $dto->color;
        $ticketStatus->icon = $dto->icon;
        $ticketStatus->display_order = $dto->displayOrder;
        $ticketStatus->is_initial = $dto->isInitial;
        $ticketStatus->is_final = $dto->isFinal;
        $ticketStatus->stops_sla = $dto->stopsSla;
        $ticketStatus->requires_comment = $dto->requiresComment;
        $ticketStatus->is_active = $dto->isActive;

        $ticketStatus->save();

        return Result::ok($ticketStatus);
    }
}