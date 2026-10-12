<?php

namespace App\Utils\Enums\Ticket;

enum TicketColumnEnum: string
{
    case ID = 'id';
    case TICKET_NUMBER = 'ticket_number';

    case USER_ID = 'user_id';
    case AGENT_ID = 'agent_id';
    case TEAM_ID = 'team_id';
    case CATEGORY_ID = 'category_id';
    case STATUS_ID = 'status_id';
    case PRIORITY_ID = 'priority_id';

    case SUBJECT = 'subject';
    case DESCRIPTION = 'description';
    case SOURCE = 'source';

    case SATISFACTION_RATING = 'satisfaction_rating';
    case IS_ESCALATED = 'is_escalated';

    case VERSION = 'version';

    case OPENED_AT = 'opened_at';
    case LAST_ACTIVITY_AT = 'last_activity_at';
    case FIRST_RESPONSE_AT = 'first_response_at';
    case RESOLVED_AT = 'resolved_at';
    case CLOSED_AT = 'closed_at';
    case DUE_AT = 'due_at';

    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';

    /**
     * Safely resolve a column name into an enum case.
     *
     * @param string|null $value Column name.
     *
     * @return self|null
     */
    public static function safeTryFrom(?string $value): ?self
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return self::tryFrom(strtolower(trim($value)));
    }
}
