<?php

namespace App\Services\TicketMessage;

use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use App\Utils\Result\Result;
use App\Utils\Snowflake\IdGeneratorInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Gate;

final class GetTicketMessageUseCase
{
    public function __construct(
        private readonly IdGeneratorInterface $idGenerator,
    ) {}

    /**
     *
     * @return array{
     *     ticketMessage: TicketMessageModel,
     *     ticket: TicketModel,
     *     replies: LengthAwarePaginator
     * }
     *
     * @throws ModelNotFoundException
     * @throws AuthorizationException
     */
    public function execute(int $id, int $perPage = 10): array
    {
        $user = auth()->user();

        if ($user === null) {
            throw new AuthorizationException(
                'Authentication is required to view ticket messages.',
            );
        }

        if (!$this->idGenerator->isValid($id)) {
            throw (new ModelNotFoundException())
                ->setModel(TicketMessageModel::class, [$id]);
        }

        $canViewInternal = $user->can(
            'ticket.message.internal.view',
        );

        $message = TicketMessageModel::query()
            ->with([
                'user',
                'ticket.user',
                'ticket.agent',
                'ticket.category',
                'ticket.priority',
                'ticket.status',
                'parentMessage' => function (BelongsTo $relation) use ($canViewInternal): void {
                    if (!$canViewInternal) {
                        $relation->where('is_internal', false);
                    }

                    $relation->with('user');
                },
            ])
            ->findOrFail($id);

        $ticket = $message->ticket;

        if ($ticket === null) {
            abort(404, 'Ticket not found.');
        }

        Gate::forUser($user)->authorize('view', $ticket);

        if ($message->is_internal && !$canViewInternal) {
            abort(404);
        }

        $threadRootId = $message->parent_id
            ?? $message->getKey();

        $repliesQuery = TicketMessageModel::query()
            ->where('ticket_id', $message->ticket_id)
            ->where('parent_id', $threadRootId)
            ->with('user');

        if ($message->parent_id !== null) {
            $repliesQuery->where('id', '<>', $message->getKey());
        }

        if (!$canViewInternal) {
            $repliesQuery->where('is_internal', false);
        }

        $replies = $repliesQuery
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate(
                max(1, min($perPage, 50)),
                ['*'],
                'replies_page',
            )
            ->withQueryString();

        return [
            'ticketMessage' => $message,
            'ticket' => $ticket,
            'replies' => $replies,
        ];
    }
}