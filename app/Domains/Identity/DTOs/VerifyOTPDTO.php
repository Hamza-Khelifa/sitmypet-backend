<?php

declare(strict_types=1);

namespace App\Domains\Identity\DTOs;

final readonly class VerifyOTPDTO
{
    public function __construct(
        public string $userId,
        public string $code,
    ) {
    }
}
