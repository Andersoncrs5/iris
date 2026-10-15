<?php 

namespace App\Http\Controllers;

use App\DTOs\Ticket\CreateTicketDTO;
use App\DTOs\Ticket\FilterTicketDTO;
use App\DTOs\Ticket\UpdateTicketDTO;
use App\DTOs\Ticket\UpdateMyOwnTicketDTO;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\UpdateMyOwnTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\CategoryModel;
use App\Models\TicketMessageModel;
use App\Models\TicketModel;
use App\Models\TicketPriorityModel;
use App\Models\TicketStatusModel;
use App\Models\User;
use App\Services\Ticket\CloseTicketUseCase;
use App\Services\Ticket\CreateTicketUseCase;
use App\Services\Ticket\DeleteOwnTicketUseCase;
use App\Services\Ticket\DeleteTicketUseCase;
use App\Services\Ticket\ListTicketUseCase;
use App\Services\Ticket\ReopenTicketUseCase;
use App\Services\Ticket\ResolvedTicketUseCase;
use App\Services\Ticket\UpdateMyOwnTicketUseCase;
use App\Services\Ticket\UpdateTicketUseCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class TicketController extends Controller
{
    public function __construct(
        private readonly CreateTicketUseCase $createTicketUseCase,
        private readonly ListTicketUseCase $listTicketUseCase,
        private readonly ReopenTicketUseCase $reopenTicketUseCase,
        private readonly CloseTicketUseCase $closeTicketUseCase,
        private readonly ResolvedTicketUseCase $resolvedTicketUseCase,
        private readonly DeleteTicketUseCase $deleteTicketUseCase,
        private readonly DeleteOwnTicketUseCase $deleteOwnTicketUseCase,
        private readonly UpdateTicketUseCase $updateTicketUseCase,
        private readonly UpdateMyOwnTicketUseCase $updateMyOwnTicketUseCase,
    ) {}

    public function listMyTicket(Request $request)
    {
        $userId = (int) auth()->user()["id"];
        
        $filterDto = FilterTicketDTO::fromRequest($request);

        $tickets = $this->listTicketUseCase->execute($filterDto, $userId);

        return $tickets;
    }

    public function list(Request $request)
    {
        Gate::authorize('ticket.read');

        $filterDto = FilterTicketDTO::fromRequest($request);

        $tickets = $this->listTicketUseCase->execute($filterDto);

        return view('ticket.list', [
            'tickets' => $tickets,
        ]);
    }

    public function close(int $id): RedirectResponse
    {
        $result = $this->closeTicketUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.show-details', $id)
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.show-details', $id)
            ->with('success', 'Ticket closed successfully!');
    }

    public function resolvedAt(int $id): RedirectResponse
    {
        $result = $this->resolvedTicketUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.show-details', $id)
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.show-details', $id)
            ->with('success', 'Ticket resolved successfully!');
    }

    public function reopen(int $id): RedirectResponse
    {
        $result = $this->reopenTicketUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.show-details', $id)
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.show-details', $id)
            ->with('success', 'Ticket reopened successfully!');
    }

    public function create(): View
    {
        Gate::authorize('ticket.create');

        $categories = CategoryModel::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'icon',
                'parent_id',
                'display_order',
            ]);

        $priorities = TicketPriorityModel::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->get([
                'id',
                'name',
                'code',
                'level',
                'color',
                'icon',
                'is_default',
                'response_time_minutes',
                'resolution_time_minutes',
                'escalation_time_minutes',
            ]);

        $initialStatus = TicketStatusModel::query()
            ->where('is_active', true)
            ->where('is_initial', true)
            ->orderBy('display_order')
            ->first([
                'id',
                'name',
                'code',
                'color',
                'icon',
            ]);

        $defaultPriorityId = $priorities
            ->firstWhere('is_default', true)
            ?->id;

        return view('ticket.create', [
            'categories' => $categories,
            'priorities' => $priorities,
            'initialStatus' => $initialStatus,
            'defaultPriorityId' => $defaultPriorityId,
        ]);
    }

    public function createForm(CreateTicketRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['user_id'] = (int) Auth::id();
        $data['source'] = 'WEB';

        $data['agent_id'] = null;

        $dto = CreateTicketDTO::fromArray($data);

        $result = $this->createTicketUseCase->execute($dto);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.create')
                ->withInput($request->except([
                    'user_id',
                    'agent_id',
                    'team_id',
                ]))
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.list')
            ->with('success', 'Ticket created successfully!');
    }

    public function edit(int $id)
    {
        Gate::authorize('ticket.update');

        $ticket = TicketModel::query()
            ->with(['user', 'agent', 'category', 'status', 'priority'])
            ->findOrFail($id);

        if ($ticket == null)
        {
            return redirect()
                ->route('ticket.list')
                ->with('error', "Ticket not found");
        }

        $categories = CategoryModel::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $priorities = TicketPriorityModel::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('level')
            ->get();

        $agents = User::query()
            ->whereNull('deleted_at')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        return view('ticket.edit', compact(
            'ticket',
            'categories',
            'priorities',
            'agents',
        ));
    }
    
    public function editForm(UpdateTicketRequest $request, int $id): RedirectResponse
    {
        $dto = UpdateTicketDTO::fromArray(
            $request->validated()
        );

        $result = $this->updateTicketUseCase->execute($dto, $id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.edit', $id)
                ->withInput()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.show-details', $id)
            ->with('success', 'Ticket updated successfully!');
    }

    public function editMyTicket(int $id) 
    {
        Gate::authorize('ticket.own.update');

        $ticket = TicketModel::query()
            ->with(['user', 'agent', 'category', 'status', 'priority'])
            ->whereKey($id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($ticket == null)
        {
            return redirect()
                ->route('ticket.list')
                ->with('error', "Ticket not found");
        }

        $categories = CategoryModel::query()
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $priorities = TicketPriorityModel::query()
            ->whereNull('deleted_at')
            ->where('is_active', true)
            ->orderBy('level')
            ->get();

        return view('ticket.edit-my-ticket', compact(
            'ticket',
            'categories',
            'priorities',
        ));
    }
    
    public function editMyTicketForm(
        UpdateMyOwnTicketRequest $request,
        int $id
    ): RedirectResponse {
        $dto = UpdateMyOwnTicketDTO::fromArray(
            $request->validated()
        );

        $result = $this->updateMyOwnTicketUseCase->execute($dto, $id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.own.edit', $id)
                ->withInput()
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.show-details', $id)
            ->with('success', 'Your ticket was updated successfully!');
    }

    public function showDetails(int $id) 
    {
        $ticket = TicketModel::find($id);

        if ($ticket == null)
        {
            return redirect()
                ->route('ticket.list')
                ->with('error', "Ticket not found");
        }

        $messages = TicketMessageModel::whereNull("parent_id")
            ->where("ticket_id", $id)->paginate();

        return view("ticket.view-details", compact("ticket", "messages"));
    }
    
    public function delete(int $id): RedirectResponse
    {
        $result = $this->deleteTicketUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.show-details', $id)
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.list')
            ->with('success', 'Ticket deleted successfully!');
    }

    public function deleteOwn(int $id): RedirectResponse
    {
        $result = $this->deleteOwnTicketUseCase->execute($id);

        if ($result->isFailure()) {
            return redirect()
                ->route('ticket.show-details', $id)
                ->with('error', $result->getError());
        }

        return redirect()
            ->route('ticket.list')
            ->with('success', 'Your ticket was deleted successfully!');
    }

}