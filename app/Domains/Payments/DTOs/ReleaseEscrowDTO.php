<?php

declare(strict_types=1);

namespace App\Domains\Payments\DTOs;

final class ReleaseEscrowDTO
{
    public function __construct(
        public readonly string $debitedWalletId,
        public readonly string $creditedWalletId,
        public readonly float $amount,
        public readonly float $platformFee,
        public readonly string $currency = 'EUR'
    ) {}
}
