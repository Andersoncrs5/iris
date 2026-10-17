<?php

namespace App\Http\Controllers;

use App\DTOs\TicketMessage\UpdateTicketMessageDTO;
use App\DTOs\TicketMessage\CreateTicketMessageDTO;
use App\Http\Requests\TicketMessage\CreateTicketMessageRequest;
use App\Http\Requests\TicketMessage\UpdateTicketMessageRequest;
use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use App\Services\TicketMessage\CreateTicketMessageUseCase;
use App\Services\TicketMessage\DeleteTicketMessageUseCase;
use App\Services\TicketMessage\GetTicketMessageUseCase;
use App\Services\TicketMessage\UpdateTicketMessageUseCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketMessageController
{
    public function __construct(
        private readonly CreateTicketMessageUseCase $createTicketMessageUseCase,
        private readonly GetTicketMessageUseCase $getTicketMessageUseCase,
        private readonly DeleteTicketMessageUseCase $deleteTicketMessageUseCase,
        private readonly UpdateTicketMessageUseCase $updateTicketMessageUseCase,
    ) {}

    public function show(int $id): View
    {
        $viewData = $this->getTicketMessageUseCase->execute($id);

        return view('ticket-message.show-details', $viewData);
    }

    public function create(int $id, Request $request): View|RedirectResponse
    {
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

    public function delete(int $id): RedirectResponse
    {
        $result = $this->deleteTicketMessageUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.list')
            ->with('success', 'Comment deleted successfully!');
    }

    public function edit(int $id)
    {
        $message = TicketMessageModel::find($id);

        if ($message == null) {
            return redirect()->back()->with("error", "Message not found");
        }

        return view("ticket-message.edit", compact("message"));
    }

    public function editForm(UpdateTicketMessageRequest $request,int $id): RedirectResponse {
        $dto = UpdateTicketMessageDTO::fromValidated(
            $request->validated()
        );

        $result = $this->updateTicketMessageUseCase->execute(
            $dto, $id
        );

        if ($result->isFailure()) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.message.edit', ['id' => $id])
            ->with('success', 'Message updated successfully.');
    }

}
