<?php

namespace App\Utils\Enums\TicketPriority;

enum TicketPriorityColumnEnum: string
{
    case ID = 'id';
    case NAME = 'name';
    case CODE = 'code';
    case LEVEL = 'level';
    case RESPONSE_TIME_MINUTES = 'response_time_minutes';
    case RESOLUTION_TIME_MINUTES = 'resolution_time_minutes';
    case ESCALATION_TIME_MINUTES = 'escalation_time_minutes';
    case IS_DEFAULT = 'is_default';
    case IS_ACTIVE = 'is_active';
    case VERSION = 'version';
    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';

    public static function safeTryFrom(?string $value): ?self
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return self::tryFrom(strtolower(trim($value)));
    }
}