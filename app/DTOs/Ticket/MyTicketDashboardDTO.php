<?php

namespace App\DTOs\Ticket;

use App\Models\TicketModel;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

final readonly class MyTicketDashboardDTO
{
    /**
     * @param EloquentCollection<int, TicketModel> $recentTickets
     * @param EloquentCollection<int, TicketModel> $attentionTickets
     */
    public function __construct(
        public int $totalTickets,
        public int $inProgressTickets,
        public int $awaitingFirstResponseTickets,
        public int $resolvedTickets,
        public int $overdueTickets,
        public int $escalatedTickets,
        public EloquentCollection $recentTickets,
        public EloquentCollection $attentionTickets,
    ) {}

    /**
     * Returns the cards displayed on the personal dashboard.
     *
     * @return array<int, array{
     *     key: string,
     *     label: string,
     *     value: int,
     *     description: string,
     *     icon: string,
     *     color: string
     * }>
     */
    public function cards(): array
    {
        return [
            [
                'key' => 'total',
                'label' => 'My tickets',
                'value' => $this->totalTickets,
                'description' => 'All active tickets',
                'icon' => 'fa-solid fa-ticket',
                'color' => 'indigo',
            ],
            [
                'key' => 'in_progress',
                'label' => 'In progress',
                'value' => $this->inProgressTickets,
                'description' => 'Tickets that received a first response',
                'icon' => 'fa-solid fa-spinner',
                'color' => 'sky',
            ],
            [
                'key' => 'awaiting_response',
                'label' => 'Awaiting first response',
                'value' => $this->awaitingFirstResponseTickets,
                'description' => 'Active tickets without a first response',
                'icon' => 'fa-regular fa-clock',
                'color' => 'amber',
            ],
            [
                'key' => 'resolved',
                'label' => 'Resolved',
                'value' => $this->resolvedTickets,
                'description' => 'Resolved or closed tickets',
                'icon' => 'fa-solid fa-circle-check',
                'color' => 'emerald',
            ],
        ];
    }

    /**
     * Indicates whether there are tickets requiring attention.
     */
    public function hasTicketsRequiringAttention(): bool
    {
        return $this->overdueTickets > 0
            || $this->escalatedTickets > 0;
    }
}