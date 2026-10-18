<?php

namespace App\Services\TicketMessage;

use App\DTOs\TicketMessage\CreateTicketMessageDTO;
use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use App\Utils\Enums\TicketMessage\TicketMessageTypeEnum;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CreateTicketMessageUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator,
    ) {}

    public function execute(CreateTicketMessageDTO $dto): Result
    {
        Gate::authorize('ticket.message.create');

        if (!$this->idGenerator->isValid($dto->ticketId)) {
            return Result::fail('Invalid ticket ID.', 400);
        }

        if (
            $dto->parentId !== null
            && !$this->idGenerator->isValid($dto->parentId)
        ) {
            return Result::fail('Invalid parent message ID.', 400);
        }

        if ($dto->type === TicketMessageTypeEnum::SYSTEM_EVENT) {
            throw ValidationException::withMessages([
                'type' => 'System events cannot be created by users.',
            ]);
        }

        if ($dto->isInternal) {
            Gate::authorize('ticket.message.internal.create');
        }

        $userId = Auth::id();

        if ($userId === null) {
            throw new AuthorizationException(
                'Authentication is required to create a ticket message.',
            );
        }

        return DB::transaction(function () use ($dto, $userId): Result {
            $ticket = TicketModel::query()
                ->findOrFail($dto->ticketId);

            Gate::authorize('view', $ticket);

            if ($dto->parentId !== null) {
                $parentMessage = TicketMessageModel::query()
                    ->where('ticket_id', $ticket->getKey())
                    ->find($dto->parentId);

                if ($parentMessage === null) {
                    throw ValidationException::withMessages([
                        'parent_id' => 'The parent message does not belong to this ticket or no longer exists.',
                    ]);
                }
            }

            $message = new TicketMessageModel([
                'ticket_id' => $ticket->getKey(),
                'user_id' => $userId,
                'message' => trim($dto->message),
                'type' => $dto->type->value,
                'is_internal' => $dto->isInternal,
                'is_edited' => false,
                'edited_at' => null,
                'attachments' => $dto->attachments,
                'version' => 1,
            ]);

            $message->id = $this->idGenerator->generate();
            $message->parent_id = $dto->parentId;
            $message->save();

            return Result::ok($message);
        });
    }
}