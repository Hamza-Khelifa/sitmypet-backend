<?php

declare(strict_types=1);

namespace App\Domains\Payments\DTOs;

final class ProcessPayOutDTO
{
    public function __construct(
        public readonly string $walletId,
        public readonly float $amount,
        public readonly string $currency = 'EUR'
    ) {}
}
