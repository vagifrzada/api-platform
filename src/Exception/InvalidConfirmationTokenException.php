<?php

declare(strict_types=1);

namespace App\Exception;

use Exception;
use JetBrains\PhpStorm\Pure;

class InvalidConfirmationTokenException extends Exception
{
    #[Pure] public static function fromMessage(string $message): self
    {
        return new self($message, 404);
    }
}