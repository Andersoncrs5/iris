<?php

namespace App\Utils\Enums\Permission;

enum PermissionColumnEnum: string
{
    case ID = 'id';
    case NAME = 'name';
    case SLUG = 'slug';
    case MODULE = 'module';
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