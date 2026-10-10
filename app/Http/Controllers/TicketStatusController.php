<?php

namespace App\Http\Controllers;

use App\DTOs\TicketStatus\CreateTicketStatusDTO;
use App\DTOs\TicketStatus\FilterTicketStatusDTO;
use App\DTOs\TicketStatus\UpdateTicketStatusDTO;
use App\Http\Requests\TicketStatus\CreateTicketStatusRequest;
use App\Http\Requests\TicketStatus\UpdateTicketStatusRequest;
use App\Models\TicketStatusModel;
use App\Services\TicketStatus\CreateTicketStatusUseCase;
use App\Services\TicketStatus\DeleteTicketStatusUseCase;
use App\Services\TicketStatus\ListTicketStatusUseCase;
use App\Services\TicketStatus\ToggleTicketStatusUseCase;
use App\Services\TicketStatus\UpdateTicketStatusUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketStatusController extends Controller
{
    public function __construct(
        private readonly CreateTicketStatusUseCase $createTicketStatusUseCase,
        private readonly ListTicketStatusUseCase $listTicketStatusUseCase,
        private readonly DeleteTicketStatusUseCase $deleteTicketUseCase,
        private readonly UpdateTicketStatusUseCase $updateTicketStatusUseCase,
        private readonly ToggleTicketStatusUseCase $toggleTicketStatusUseCase,
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('ticket.status.list');

        $filterDto = FilterTicketStatusDTO::fromRequest($request);

        $tickets = $this->listTicketStatusUseCase->execute($filterDto);

        return view('ticket-status.list', [
            'tickets' => $tickets,
        ]);
    }

    public function create()
    {
        Gate::authorize('ticket.status.create');

        return view('ticket-status.create');
    }

    public function createForm(CreateTicketStatusRequest $request)
    {
        Gate::authorize('ticket.status.create');

        $dto = CreateTicketStatusDTO::fromRequest($request);

        $result = $this->createTicketStatusUseCase->execute($dto);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.status.list')
                ->with('error', $result->getError())
                ->withInput();
        }

        return redirect()
            ->route('ticket.status.list')
            ->with('success', 'Ticket status created successfully!');
    }

    public function delete(int $id)
    {
        Gate::authorize('ticket.status.delete');

        $result = $this->deleteTicketUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.status.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.status.list')
            ->with('success', 'Ticket status deleted with success!');
    }

    public function edit(int $id) 
    {
        Gate::authorize('ticket.status.update');

        $ticket = TicketStatusModel::find($id);

        if ($ticket == null) {
            return redirect()
                ->route('ticket.status.list')
                ->with('error', "Ticket status not found");
        }

        
        return view("ticket-status.edit", compact("ticket"));
    }

    public function editForm(UpdateTicketStatusRequest $request, int|string $id) 
    {
        Gate::authorize('ticket.status.update');

        $dto = UpdateTicketStatusDTO::fromRequest($request, $id);
        $result = $this->updateTicketStatusUseCase->execute($dto, $id);

        if ($result->isFailure()) {
            return redirect()->back()->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.status.list')
            ->with('success', 'Ticket status updated successfully!');
    }

    public function toggleActive(int $id)
    {
        Gate::authorize('ticket.status.update');

        $result = $this->toggleTicketStatusUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.status.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.status.list')
            ->with('success', 'Ticket Status toggled');
    }

    public function showDetails(int $id)
    {
        $ticket = TicketStatusModel::find($id);

        if ($ticket == null) {
            return redirect()
                ->route('ticket.status.list')
                ->with('error', "Ticket status not found");
        }

        return view("ticket-status.view-details", compact("ticket"));
    }

}