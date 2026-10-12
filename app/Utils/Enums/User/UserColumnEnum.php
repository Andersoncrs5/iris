<?php

namespace App\Utils\Enums\User;

enum UserColumnEnum: string
{
    case ID = 'id';
    case NAME = 'name';
    case FULL_NAME = 'full_name';
    case EMAIL = 'email';
    case STATUS = 'status';
    case EMAIL_VERIFIED_AT = 'email_verified_at';
    case LAST_LOGIN_AT = 'last_login_at';
    case VERSION = 'version';
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
