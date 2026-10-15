<?php

namespace App\Policies;

use App\Models\TicketModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketModelPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TicketModel $ticketModel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TicketModel $ticketModel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TicketModel $ticketModel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TicketModel $ticketModel): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TicketModel $ticketModel): bool
    {
        return false;
    }

    public function updateOwn(User $user, TicketModel $ticket): bool
    {
        return $user->can('ticket.own.update')
            && (string) $ticket->user_id === (string) $user->getAuthIdentifier();
    }
}
