<?php 

namespace App\Http\Controllers;

use App\DTOs\Ticket\CreateTicketDTO;
use App\DTOs\Ticket\FilterTicketDTO;
use App\Http\Requests\Ticket\CreateTicketRequest;
use App\Http\Requests\Ticket\UpdateTicketRequest;
use App\Models\CategoryModel;
use App\Models\TicketModel;
use App\Models\TicketPriorityModel;
use App\Models\TicketStatusModel;
use App\Services\Ticket\CreateTicketUseCase;
use App\Services\Ticket\ListTicketUseCase;
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
    ) {}

    public function list(Request $request)
    {
        Gate::authorize('ticket.read');

        $filterDto = FilterTicketDTO::fromRequest($request);

        $tickets = $this->listTicketUseCase->execute($filterDto);

        return view('ticket.list', [
            'tickets' => $tickets,
        ]);
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

    public function edit() 
    {
        return "DENTRO DO FORM PARA EDIT TICKE";
    }
    
    public function editForm(UpdateTicketRequest $request, int $id) 
    {
        
    }

    public function editMyTicket() 
    {
        return "DENTRO DO FORM PARA EDIT TICKE";
    }
    
    public function editMyTicketForm(UpdateTicketRequest $request, int $id) 
    {
        
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

        return view("ticket.view-details", compact("ticket"));
    }
    
    public function delete(int $id) 
    {
        return "DENTRO DO delete";
    }
    
    
}