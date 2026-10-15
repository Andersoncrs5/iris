<?php

namespace App\Http\Controllers;

use App\DTOs\TicketMessage\CreateTicketMessageDTO;
use App\Http\Requests\TicketMessage\CreateTicketMessageRequest;
use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use App\Services\TicketMessage\CreateTicketMessageUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketMessageController
{
    public function __construct(
        private readonly CreateTicketMessageUseCase $createTicketMessageUseCase,
    ) {}

    public function show(int $id)
    {
        return $id;
    }

    public function create(int $id, Request $request): View|RedirectResponse {
        $ticket = TicketModel::find($id);

        if ($ticket === null) {
            return redirect()
                ->back()
                ->with('error', 'Ticket not found.');
        }

        $validated = $request->validate([
            'parentId' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        $parentMessage = null;
        $parentId = $validated['parentId'] ?? null;

        if ($parentId !== null) {
            $parentMessage = TicketMessageModel::query()
                ->where('ticket_id', $ticket->id)
                ->find($parentId);

            if ($parentMessage === null) {
                return redirect()
                    ->route('ticket.message.create', ['id' => $ticket->id])
                    ->with('error', 'The message you want to reply to was not found.');
            }
        }

        return view('ticket-message.create', [
            'ticket' => $ticket,
            'parentMessage' => $parentMessage,
        ]);
    }

    public function createForm(CreateTicketMessageRequest $request, int $id ): RedirectResponse {
        $ticket = TicketModel::find($id);

        if ($ticket === null) {
            return redirect()
                ->back()
                ->with('error', 'Ticket not found.');
        }

        
        $dto = CreateTicketMessageDTO::fromValidated(
            $request->validated(),
            (int) $ticket->id,
        );

        $result = $this->createTicketMessageUseCase->execute($dto);

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->with('error', $result->getError());
        }

        return redirect()
            ->back()
            ->with('success', 'Comment added successfully.');
    }
    
}