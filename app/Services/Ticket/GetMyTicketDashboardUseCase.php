<?php

namespace App\Services\Ticket;

use App\DTOs\Ticket\MyTicketDashboardDTO;
use App\Models\TicketModel;
use InvalidArgumentException;

class GetMyTicketDashboardUseCase
{
    public function execute(int $userId): MyTicketDashboardDTO
    {
        if ($userId <= 0) {
            throw new InvalidArgumentException(
                'A valid user ID is required.'
            );
        }

        $now = now();

        /*
         * Calculate metrics directly in PostgreSQL.
         *
         * In this dashboard:
         * - In progress: active and already received a first response.
         * - Awaiting first response: active without a first response.
         * - Resolved: resolution or closure timestamp is present.
         */
        $metrics = TicketModel::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) AS total_tickets')
            ->selectRaw(
                'COUNT(*) FILTER (
                    WHERE resolved_at IS NULL
                    AND closed_at IS NULL
                    AND first_response_at IS NULL
                ) AS awaiting_first_response_tickets'
            )
            ->selectRaw(
                'COUNT(*) FILTER (
                    WHERE resolved_at IS NULL
                    AND closed_at IS NULL
                    AND first_response_at IS NOT NULL
                ) AS in_progress_tickets'
            )
            ->selectRaw(
                'COUNT(*) FILTER (
                    WHERE resolved_at IS NOT NULL
                    OR closed_at IS NOT NULL
                ) AS resolved_tickets'
            )
            ->selectRaw(
                'COUNT(*) FILTER (
                    WHERE due_at IS NOT NULL
                    AND due_at < ?
                    AND resolved_at IS NULL
                    AND closed_at IS NULL
                ) AS overdue_tickets',
                [$now]
            )
            ->selectRaw(
                'COUNT(*) FILTER (
                    WHERE is_escalated IS TRUE
                    AND resolved_at IS NULL
                    AND closed_at IS NULL
                ) AS escalated_tickets'
            )
            ->firstOrFail();

        $recentTickets = TicketModel::query()
            ->where('user_id', $userId)
            ->with([
                'status',
                'priority',
                'category',
            ])
            ->orderByDesc('last_activity_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $attentionTickets = TicketModel::query()
            ->where('user_id', $userId)
            ->whereNull('resolved_at')
            ->whereNull('closed_at')
            ->where(function ($query) use ($now) {
                $query
                    ->where(function ($dueQuery) use ($now) {
                        $dueQuery
                            ->whereNotNull('due_at')
                            ->where('due_at', '<', $now);
                    })
                    ->orWhere('is_escalated', true);
            })
            ->with([
                'status',
                'priority',
                'category',
            ])
            ->orderByRaw(
                'CASE WHEN due_at IS NOT NULL AND due_at < ? THEN 0 ELSE 1 END',
                [$now]
            )
            ->orderBy('due_at')
            ->orderByDesc('last_activity_at')
            ->limit(5)
            ->get();

        return new MyTicketDashboardDTO(
            totalTickets: (int) $metrics->total_tickets,
            inProgressTickets: (int) $metrics->in_progress_tickets,
            awaitingFirstResponseTickets: (int) $metrics->awaiting_first_response_tickets,
            resolvedTickets: (int) $metrics->resolved_tickets,
            overdueTickets: (int) $metrics->overdue_tickets,
            escalatedTickets: (int) $metrics->escalated_tickets,
            recentTickets: $recentTickets,
            attentionTickets: $attentionTickets,
        );
    }
}