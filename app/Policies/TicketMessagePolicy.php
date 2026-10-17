<?php

namespace App\Policies;

use App\Models\TicketMessageModel;
use App\Models\User;

/**
 * Authorization rules for ticket messages.
 */
final class TicketMessagePolicy
{
    public function update(
        User $user,
        TicketMessageModel $ticketMessage,
    ): bool {
        return $this->isOwner($user, $ticketMessage);
    }

    public function delete(
        User $user,
        TicketMessageModel $ticketMessage,
    ): bool {
        return $this->isOwner($user, $ticketMessage);
    }

    private function isOwner(
        User $user,
        TicketMessageModel $ticketMessage,
    ): bool {
        return (string) $ticketMessage->user_id
            === (string) $user->getAuthIdentifier();
    }
}