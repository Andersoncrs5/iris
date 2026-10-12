<?php

namespace App\Http\Controllers;

use App\DTOs\TicketPriority\CreateTicketPriorityDTO;
use App\DTOs\TicketPriority\FilterTicketPriorityDTO;
use App\DTOs\TicketPriority\UpdateTicketPriorityDTO;
use App\Http\Requests\TicketPriority\CreateTicketPriorityRequest;
use App\Http\Requests\TicketPriority\UpdateTicketPriorityRequest;
use App\Models\TicketPriorityModel;
use App\Services\TicketPriority\CreateTicketPriorityUseCase;
use App\Services\TicketPriority\DeleteTicketPriorityUseCase;
use App\Services\TicketPriority\ListTicketPriorityUseCase;
use App\Services\TicketPriority\UpdateTicketPriorityUseCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TicketPriorityController extends Controller
{
    public function __construct(
        private readonly CreateTicketPriorityUseCase $createTicketPriorityUseCase,
        private readonly ListTicketPriorityUseCase $listTicketPriorityUseCase,
        private readonly DeleteTicketPriorityUseCase $deleteTicketPriorityUseCase,
        private readonly UpdateTicketPriorityUseCase $updateTicketPriorityUseCase,
    ) {}

    public function list(Request $request)
    {
        Gate::authorize('ticket.priority.read');

        $filterDto = FilterTicketPriorityDTO::fromRequest($request);

        $priorities = $this->listTicketPriorityUseCase->execute($filterDto);

        return view('ticket-priority.list', [
            'priorities' => $priorities,
        ]);
    }

    public function create()
    {
        Gate::authorize('ticket.priority.create');
        
        return view("ticket-priority.create");
    }
    
    public function createForm(CreateTicketPriorityRequest $request)
    {
        $dto = CreateTicketPriorityDTO::fromArray(
            $request->validated()
        );

        $result = $this->createTicketPriorityUseCase->execute($dto);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.priority.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.priority.list')
            ->with('success', 'Ticket priority created successfully!');
    }

    public function edit(int $id)
    {
        $priority = TicketPriorityModel::find($id);

        if ($priority == null) {
            return redirect()
                ->route('ticket.priority.list')
                ->with('error', "Ticket priority not found");
        }

        return view("ticket-priority.edit", compact("priority"));
    }

    public function editForm(UpdateTicketPriorityRequest $request, int $id)
    {
        $dto = UpdateTicketPriorityDTO::fromArray(
            $request->validated()
        );

        $result = $this->updateTicketPriorityUseCase->execute($dto, $id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.priority.edit', $id)
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.priority.list')
            ->with('success', 'Ticket priority updated successfully!');
    }

    public function delete(int $id)
    {
        $result = $this->deleteTicketPriorityUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.priority.list')
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.priority.list')
            ->with('success', 'Ticket priority deleted successfully!');
    }

    public function showDetails(int $id)
    {
        $priority = TicketPriorityModel::find($id);

        if ($priority == null) {
            return redirect()
                ->route('ticket.priority.list')
                ->with('error', "Ticket not found");
        }

        return view("ticket-priority.show-details", compact("priority"));
    }
}