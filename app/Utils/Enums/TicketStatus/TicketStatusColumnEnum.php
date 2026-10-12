<?php

namespace App\Utils\Enums\TicketStatus;

enum TicketStatusColumnEnum: string 
{
    case ID = 'id';
    case NAME = 'name';
    case CODE = 'code';
    case DISPLAY_ORDER = 'display_order';
    case VERSION = 'version';
    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';
    
    public static function safeTryFrom(?string $value): ?self
    {
        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }
        return null;
    }
}