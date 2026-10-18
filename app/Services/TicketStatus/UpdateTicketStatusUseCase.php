<?php

namespace App\Services\TicketStatus;

use App\DTOs\TicketStatus\UpdateTicketStatusDTO;
use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class UpdateTicketStatusUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(
        UpdateTicketStatusDTO $dto,
        int|string $id
    ): Result {
        $validatedId = filter_var($id, FILTER_VALIDATE_INT);

        if (
            $validatedId === false
            || !$this->idGenerator->isValid($validatedId)
        ) {
            return Result::fail('Invalid ticket status ID.', 400);
        }

        try {
            return DB::transaction(function () use (
                $dto,
                $validatedId
            ): Result {
                $ticketStatus = TicketStatusModel::query()
                    ->whereKey($validatedId)
                    ->lockForUpdate()
                    ->first();

                if ($ticketStatus === null) {
                    return Result::fail('Ticket status not found.', 404);
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
            }, 3);
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the ticket status.'
            );
        }
    }
}