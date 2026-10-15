<?php

namespace App\Http\Controllers;

use App\DTOs\Ticket\FilterTicketDTO;
use App\Models\CategoryModel;
use App\Models\TicketPriorityModel;
use App\Models\TicketStatusModel;
use App\Services\Ticket\GetMyTicketDashboardUseCase;
use App\Services\Ticket\ListTicketUseCase;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GetMyTicketDashboardUseCase $getMyTicketDashboardUseCase,
        private readonly ListTicketUseCase $listTicketUseCase,
    ) {}

    public function index(Request $request): View
    {
        $userId = (int) auth()->id();

        // Dashboard summary metrics are independent from list filters.
        $dashboard = $this->getMyTicketDashboardUseCase->execute($userId);

        // Reuse the existing ticket filter DTO and listing use case,
        // restricting the result set to the authenticated user.
        $filterDto = FilterTicketDTO::fromRequest($request);
        $tickets = $this->listTicketUseCase->execute($filterDto, $userId);

        $filterCategories = CategoryModel::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $filterPriorities = TicketPriorityModel::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->get(['id', 'name', 'level']);

        $filterStatuses = TicketStatusModel::query()
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('dashboard.main', compact(
            'dashboard',
            'tickets',
            'filterCategories',
            'filterPriorities',
            'filterStatuses',
        ));
    }
}
