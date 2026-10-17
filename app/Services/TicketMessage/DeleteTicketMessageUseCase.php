<?php

namespace App\Services\TicketMessage;

use App\Models\TicketMessageModel;
use App\Utils\Result\Result;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteTicketMessageUseCase
{
    public function execute(int $id): Result
    {
        try {
            return DB::transaction(function () use ($id): Result {
                $message = TicketMessageModel::query()
                    ->lockForUpdate()
                    ->find($id);

                if ($message === null) {
                    return Result::fail('Comment not found.');
                }

                Gate::authorize('delete', $message);

                if ($message->replies()->exists()) {
                    return Result::fail(
                        'This comment cannot be deleted because it has replies.',
                    );
                }

                $message->delete();

                return Result::ok($message);
            });
        } catch (QueryException $exception) {
            report($exception);

            return Result::fail(
                'Unable to delete the comment due to a database error.',
            );
        }
    }
}