<?php

declare(strict_types=1);

namespace App\Domains\Marketplace\Exceptions;

use Exception;

class InvalidStateException extends Exception
{
    public static function transitionNotAllowed(string $model, string $from, string $to): self
    {
        return new self("Transition from '{$from}' to '{$to}' is not allowed for model {$model}.");
    }
}
