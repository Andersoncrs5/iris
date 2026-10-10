<?php

namespace App\Utils\Enums\Category;

enum CategoryColumnEnum: string
{
    case ID = 'id';
    case NAME = 'name';
    case SLUG = 'slug';
    case DESCRIPTION = 'description';
    case PARENT_ID = 'parent_id';
    case ICON = 'icon';
    case DISPLAY_ORDER = 'display_order';
    case IS_ACTIVE = 'is_active';
    case VERSION = 'version';
    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';
    case DELETED_AT = 'deleted_at';

    /**
      * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}