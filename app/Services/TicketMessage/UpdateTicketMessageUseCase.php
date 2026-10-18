<?php

namespace App\Services\TicketMessage;

use App\DTOs\TicketMessage\UpdateTicketMessageDTO;
use App\Models\TicketMessageModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class UpdateTicketMessageUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator,
    ) {}

    public function execute(
        UpdateTicketMessageDTO $dto,
        int $id,
    ): Result {
        Gate::authorize('update', $id);

        if (!$this->idGenerator->isValid($id)) {
            return Result::fail('Invalid ticket message ID.', 400);
        }

        try {
            return DB::transaction(function () use ($dto, $id): Result {
                $message = TicketMessageModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($message === null) {
                    return Result::fail('Message not found.');
                }

                Gate::authorize('update', $message);

                $message->fill($dto->toArray());

                $message->is_edited = true;
                $message->edited_at = now();
                $message->version = (int) $message->version + 1;

                $message->save();

                return Result::ok($message);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'An unexpected database error occurred while updating the message.'
            );
        }
    }
}