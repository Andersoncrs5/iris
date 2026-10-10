<?php

namespace App\Utils\Enums\Tag;

enum TagColumnEnum: string
{
    case ID = 'id';
    case NAME = 'name';
    case SLUG = 'slug';
    case COLOR = 'color';
    case TYPE = 'type';
    case USAGE_COUNT = 'usage_count';
    case IS_ACTIVE = 'is_active';
    case CREATED_AT = 'created_at';
    case UPDATED_AT = 'updated_at';
}