<?php

declare(strict_types=1);

namespace App\Enum;

use MyCLabs\Enum\Enum;

final class UserRole extends Enum
{
    public const COMMENTATOR = 'ROLE_COMMENTATOR';
    public const WRITER = 'ROLE_WRITER';
    public const EDITOR = 'ROLE_EDITOR';
    public const ADMIN = 'ROLE_ADMIN';
    public const SUPERADMIN = 'ROLE_SUPERADMIN';

    public static function getDefaultRoles(): array
    {
        return [
            self::COMMENTATOR,
        ];
    }

    public static function writerRoles(): array
    {
        return [
            self::SUPERADMIN,
            self::ADMIN,
            self::WRITER,
        ];
    }

    public static function commentatorRoles(): array
    {
        return [
            self::SUPERADMIN,
            self::ADMIN,
            self::WRITER,
            self::COMMENTATOR,
        ];
    }
}
