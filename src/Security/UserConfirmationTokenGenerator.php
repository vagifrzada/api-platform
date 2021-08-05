<?php

declare(strict_types=1);

namespace App\Security;

use Exception;

class UserConfirmationTokenGenerator
{
    public static function generate(int $bytes = 32): string
    {
        try {
            return bin2hex(random_bytes($bytes));
        } catch (Exception) {
            return bin2hex(openssl_random_pseudo_bytes($bytes));
        }
    }
}