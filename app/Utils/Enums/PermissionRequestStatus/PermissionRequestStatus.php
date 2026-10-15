<?php

namespace App\Utils\Enums\PermissionRequestStatus;

enum PermissionRequestStatus: string
{
    case PENDING = 'pending';

    case APPROVED = 'approved';

    case REJECTED = 'rejected';

    case CANCELLED = 'cancelled';

    /**
     *
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $status): string => $status->value,
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pendente',
            self::APPROVED => 'Aprovada',
            self::REJECTED => 'Rejeitada',
            self::CANCELLED => 'Cancelada',
        };
    }
}