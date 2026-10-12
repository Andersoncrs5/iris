<?php

namespace App\Services\TicketStatus;

use App\DTOs\TicketStatus\CreateTicketStatusDTO;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Support\Facades\Gate;

class CreateTicketStatusUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator
    ) {}

    public function execute(CreateTicketStatusDTO $dto): Result 
    {
        Gate::authorize('ticket.status.create');
        
        $status = $dto->toModel();
        $status->id = $this->idGenerator->generate();

        $status->save();

        return Result::ok($status);
    }
}