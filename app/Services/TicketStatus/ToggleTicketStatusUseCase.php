<?php

namespace App\Services\TicketStatus;

use App\Models\TicketStatusModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\DB;

class ToggleTicketStatusUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(int $id): Result
    {
        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket status ID.', 400);
        }

        return DB::transaction(function () use ($id): Result {
            $ticket = TicketStatusModel::query()
                ->lockForUpdate()
                ->find($id);

            if ($ticket === null) {
                return Result::fail('Ticket Status not found.', 404);
            }

            $ticket->is_active = !(bool) $ticket->is_active;
            $ticket->save();

            return Result::ok($ticket);
        }, 3);
    }
}